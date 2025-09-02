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
        if ($message) {
            $this->contactManager->delete($message);
        }
        header("Location: /admin/messages");
        exit();
    }
}

