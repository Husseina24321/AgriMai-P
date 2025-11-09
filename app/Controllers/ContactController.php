<?php
namespace app\Controllers;
use app\Managers\ContactManager;
class ContactController extends AbstractController
{
    private ContactManager $contactManager;

    public function __construct()
    {
        parent::__construct();
        $this->contactManager = new ContactManager();
    }

    private function validateFields(array $fields, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($fields[$field])) {
                $errors[] = "Le champ '$field' est obligatoire.";
            }
            if ($rule === 'email' && !empty($fields[$field]) && !filter_var($fields[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Le champ '$field' doit être un email valide.";
            }
        }

        return $errors;
    }

    /**
     * Affiche le formulaire de contact
     */
    public function showForm(array $fields = [], array $errors = []): void
    {
        $this->render("/front/contact.html.twig", [
            "fields" => $fields,
            "errors" => $errors
        ]);
    }


    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showForm();
            return;
        }

        $fields = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'message' => trim($_POST['message'] ?? '')
        ];

        $rules = [
            'name'    => 'required',
            'email'   => 'email',
            'message' => 'required'
        ];

        $errors = $this->validateFields($fields, $rules);

        $receiverId = 1; // id d’un compte admin ou producteur principal
        $receiver = $this->contactManager->getUserById($receiverId);
        if (!$receiver) {
            $errors[] = "Le destinataire du message est introuvable (id $receiverId).";
        }

        if (!empty($errors)) {
            $this->showForm($fields, $errors);
            return;
        }

        $content = htmlspecialchars($fields['message'], ENT_QUOTES, 'UTF-8');

        $senderId = $_SESSION['user']['id'] ?? 0;

        $this->contactManager->create([
            "sender_id"   => $senderId,
            "receiver_id" => $receiverId,
            "product_id"  => null,
            "content"     => $content
        ]);

        // Envoi d’un mail au destinataire
        if ($receiver && isset($receiver['email'])) {
            $subject = "Nouveau message reçu depuis le formulaire de contact";
            $body = "Nom : {$fields['name']}\nEmail : {$fields['email']}\n\nMessage :\n{$fields['message']}";
            mail($receiver['email'], $subject, $body);
        }

        // Redirection
        header("Location: /AgriMai/index.php?route=successMessage");
        exit;
    }


    public function successMessage(): void
    {
        $this->render("front/successMessage.html.twig");
    }


    private function getMessagesForCurrentUser(): array
    {
        $userId = $_SESSION['user']['id'];
        $receiverId = isset($_GET['receiver_id']) ? (int) $_GET['receiver_id'] : null;

        if ($receiverId) {
            $messages = $this->contactManager->findMessagesByUserPair($userId, $receiverId);
        } else {
            $received = $this->contactManager->findByReceiverId($userId);
            $sent     = $this->contactManager->findBySenderId($userId);
            $messages = array_merge($received, $sent);
        }

        // Trier par date ASC pour conversation chronologique, on faire cela directement par la requette via le manager avec la notion By
        usort($messages, fn($a, $b) => $a->getSentAt() <=> $b->getSentAt());

        $result = [];
        foreach ($messages as $msg) {
            // Récupérer le nom du destinataire si possible
            $receiver = $this->contactManager->getUserById($msg->getReceiverId());

            $result[] = [
                'id' => $msg->getId(),
                'senderId' => $msg->getSenderId(),
                'receiverId' => $msg->getReceiverId(),
                'receiverFirstName' => $receiver['first_name'] ?? '',
                'receiverLastName'  => $receiver['last_name'] ?? '',
                'content' => $msg->getContent(),
                'sentAt' => $msg->getSentAt(),
                'is_sender' => $msg->getSenderId() === $userId,
                'senderFirstName' => $msg->getSenderFirstName(),
                'senderLastName' => $msg->getSenderLastName(),
                'productTitle' => $msg->getProductTitle(),
                'productId' => $msg->getProductId()
            ];
        }


        return $result;
    }

    private function getMessagesWithReceiver(): array
    {
        $messages = $this->getMessagesForCurrentUser();
        $receiver_id = null;
        $product_id  = null;

        if (!empty($messages)) {
            $lastMessage = end($messages);
            $receiver_id = $lastMessage['is_sender'] ? $lastMessage['receiverId'] : $lastMessage['senderId'];
            $product_id  = $lastMessage['productId'];
        }

        return [
            'messages' => $messages,
            'receiver_id' => $receiver_id,
            'product_id' => $product_id
        ];
    }

    public function listMessagesByProducer(): void
    {  $data = $this->getMessagesWithReceiver();
        $this->render("/admin/producerMessages.html.twig", $data);
    }

    public function listMessagesByBuyer(): void
    {
        $data = $this->getMessagesWithReceiver();
        $this->render("/admin/buyerMessages.html.twig", $data);
    }




    // Liste tous les messages
    public function listMessages(): void
    {
        $messages = $this->contactManager->findAll();
        $this->render("admin/messages/list", ["messages" => $messages]);
    }

    // Liste les messages reçus par un utilisateur
    public function listMessagesByUser(int $userId): void
    {
        $messages = $this->contactManager->findByReceiverId($userId);
        $this->render("admin/messages/user", ["messages" => $messages]);
    }



    public function updateMessageProducer(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $content = $_POST['message'];

            $this->contactManager->updateMessage($id, $content);

            $user = $_SESSION['user'] ?? null;

            if ($user && $user['role'] === 'Producteur') {
                header("Location: /AgriMai/index.php?route=producerMessages");
            } elseif ($user && $user['role'] === 'Acheteur') {
                header("Location: /AgriMai/index.php?route=buyerMessages");
            } else {
                header("Location: /AgriMai/index.php?route=home");
            }

            exit();
        }
    }


    public function editMessageProducer(): void
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            echo "ID du message manquant.";
            return;
        }

        $messageId = (int)$_GET['id'];
        $message = $this->contactManager->findById($messageId);

        if (!$message) {
            echo "Message introuvable.";
            return;
        }

        $user = $_SESSION['user'] ?? null;

        if ($user && $user['role'] === 'Producteur') {
            $backRoute = "/AgriMai/index.php?route=producerMessages";
        } elseif ($user && $user['role'] === 'Acheteur') {
            $backRoute = "/AgriMai/index.php?route=buyerMessages";
        } else {
            $backRoute = "/AgriMai/index.php?route=home";
        }

        $this->render("/admin/editMessages.html.twig", [
            "message"     => $message,
            "actionRoute" => "/AgriMai/index.php?route=updateMessageProducer",
            "backRoute"   => $backRoute
        ]);
    }


    public function editMessageBuyer(): void
    {
        $this->requireLogin();

        if (!isset($_GET['id'])) {
            echo "ID du message manquant.";
            return;
        }

        $messageId = (int)$_GET['id'];
        $message = $this->contactManager->findById($messageId);

        if (!$message) {
            echo "Message introuvable.";
            return;
        }

        $user = $_SESSION['user'] ?? null;

        if ($user && $user['role'] === 'Acheteur') {
            $backRoute = "/AgriMai/index.php?route=buyerMessages";
        } elseif ($user && $user['role'] === 'Producteur') {
            $backRoute = "/AgriMai/index.php?route=producerMessages";
        } else {
            $backRoute = "/AgriMai/index.php?route=home";
        }

        $this->render("/admin/editMessages.html.twig", [
            "message"     => $message,
            "actionRoute" => "/AgriMai/index.php?route=updateMessageBuyer",
            "backRoute"   => $backRoute
        ]);
    }
    public function updateMessageBuyer(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $content = $_POST['message'];

            $this->contactManager->updateMessage($id, $content);

            $user = $_SESSION['user'] ?? null;

            if ($user && $user['role'] === 'Acheteur') {
                header("Location: /AgriMai/index.php?route=buyerMessages");
            } elseif ($user && $user['role'] === 'Producteur') {
                header("Location: /AgriMai/index.php?route=producerMessages");
            } else {
                header("Location: /AgriMai/index.php?route=home");
            }

            exit();
        }
    }



    // Supprimer un message
    public function deleteMessage(): void
    {
        if (!isset($_GET['id'])) {
            header("Location: /AgriMai/index.php?route=producerMessages");
            exit();
        }

        $messageId = (int) $_GET['id'];
        $this->contactManager->deleteMessage($messageId);

        // Redirection après suppression
        header("Location: /AgriMai/index.php?route=producerMessages");
        exit();
    }




}