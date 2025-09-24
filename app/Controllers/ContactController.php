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

    /**
     * Validation des champs de formulaire
     */
    private function validateFields(array $fields, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = trim($fields[$field] ?? '');

            if ($rule === 'required' && empty($value))
                $errors[] = "Le champ $field est obligatoire.";
            elseif ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL))
                $errors[] = "Le champ $field doit être un email valide.";
        }

        return $errors;
    }

    /**
     * Affiche le formulaire de contact
     */
    public function showForm(array $old = [], array $errors = [], bool $success = false): void
    {
        $this->render("front/contact.html.twig", [
            "old"     => $old,
            "errors"  => $errors,
            "success" => $success
        ]);
    }

    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showForm();
            return;
        }

        $fields = [
            'name'    => $_POST['name'] ?? '',
            'email'   => $_POST['email'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];

        $rules = [
            'name'    => 'required',
            'email'   => 'email',
            'message' => 'required'
        ];

        $errors = $this->validateFields($fields, $rules);

        if (!empty($errors)) {
            $this->showForm($fields, $errors);
            return;
        }

        $content = "Nom: {$fields['name']}\nEmail: {$fields['email']}\n\n{$fields['message']}";

        $this->contactManager->create([
            "sender_id"   => 0, // remplace null pour éviter ton erreur PDO
            "receiver_id" => 1,
            "content"     => $content
        ]);

        header("Location: /AgriMai/index.php?route=successMessage");
        exit();
    }
    public function successMessage(): void
    {
        $this->render("front/successMessage.html.twig", [
            "message" => "Eh ! Nous avons bien reçu votre message, nous reviendrons vers vous très bientôt."
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