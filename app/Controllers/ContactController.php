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

        // Récupération des données du formulaire
        $fields = [
            'name'        => trim($_POST['name'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'message'     => trim($_POST['message'] ?? ''),
            'receiver_id' => $_POST['receiver_id'] ?? null,
            'product_id'  => $_POST['product_id'] ?? null
        ];

        // Validation des champs
        $rules = [
            'name'    => 'required',
            'email'   => 'email',
            'message' => 'required'
        ];

        $errors = $this->validateFields($fields, $rules);

        // Vérifie que le destinataire existe
        $receiver = null;
        if ($fields['receiver_id']) {
            $receiver = $this->contactManager->getUserById((int)$fields['receiver_id']);
            if (!$receiver) {
                $errors[] = "Le destinataire du message n'existe pas.";
            }
        } else {
            $errors[] = "Aucun destinataire sélectionné.";
        }

        if (!empty($errors)) {
            $this->showForm($fields, $errors);
            return;
        }

        // Prépare le contenu du message
        $content = $fields['message']; // juste le texte du message

        // ID de l'expéditeur connecté (acheteur ou producteur)
        $senderId = $_SESSION['user']['id'] ?? 0;

        // Création du message
        $this->contactManager->create([
            "sender_id"   => $senderId,
            "receiver_id" => (int)$fields['receiver_id'],
            "product_id"  => !empty($fields['product_id']) ? (int)$fields['product_id'] : null,
            "content"     => $content
        ]);

        // Si le destinataire a un email, on peut lui envoyer une notification
        if ($receiver && isset($receiver['email'])) {
            $subject = "Nouveau message reçu";
            $body = "Vous avez reçu un nouveau message :\n\n" . $fields['message'];
            mail($receiver['email'], $subject, $body);
        }

        // Redirection vers la page de succès
        $this->render("/front/successMessage.html.twig", [
            "name" => $fields['name']
        ]);
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

        // Trier par date ASC pour conversation chronologique
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $content = $_POST['message'];

            $this->contactManager->updateMessage($id, $content);

            // Redirection vers la liste des messages du producteur
            header("Location: /AgriMai/index.php?route=producerMessages");
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

        $this->render("/admin/editMessages.html.twig", [
            "message" => $message,
            "actionRoute" => "/AgriMai/index.php?route=updateMessageProducer",
            "backRoute" => "/AgriMai/index.php?route=producerMessages"
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

        $this->render("/admin/editMessages.html.twig", [
            "message"     => $message,
            "actionRoute" => "/AgriMai/index.php?route=updateMessageBuyer",
            "backRoute"   => "/AgriMai/index.php?route=buyerMessages"
        ]);
    }

    public function updateMessageBuyer(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $content = $_POST['message'];

            $this->contactManager->updateMessage($id, $content);

            header("Location: /AgriMai/index.php?route=buyerMessages");
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
        // Supprime le message
        $this->contactManager->deleteMessage($messageId);

        // Redirection après suppression
        $route = 'messages'; // ou 'product' si tu veux rediriger vers un produit
        header("Location: /AgriMai/index.php?route=producerMessages");
        exit();


    }




}