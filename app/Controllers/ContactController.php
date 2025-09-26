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
        $content = "Nom: {$fields['name']}\nEmail: {$fields['email']}\n\n{$fields['message']}";

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

    public function listMessagesByProducer(): void
    {
        $producerId = $_SESSION['user']['id'] ?? 0;
        $messages = $this->contactManager->findMessagesForProducer($producerId);


        $this->render("admin/producerMessages.html.twig", [
            "messages" => $messages
        ]);
    }
    public function listMessagesByBuyer(): void
    {
        $this->requireLogin();

        $userId = $_SESSION['user']['id'];
        $messages = $this->contactManager->findByReceiverId($userId);

        $this->render("/admin/buyerMessages.html.twig", [
            "messages" => $messages
        ]);
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

    // Supprimer un message
    public function deleteMessage(int $id): void
    {
        $message = $this->contactManager->findById($id);
        if ($message)
            $this->contactManager->delete($message);

        header("Location: /admin/messages");
        exit();
    }
}