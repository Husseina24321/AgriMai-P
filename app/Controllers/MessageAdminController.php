<?php
namespace app\Controllers;
use app\Managers\ContactManager;


class MessageAdminController extends AbstractController
{
    private ContactManager $cm;

    public function __construct()
    {
        parent::__construct();
        $this->cm = new ContactManager();
    }

    // Afficher tous les messages
    public function listAdminMessage(): void
    {
        $messages = $this->cm->findAll();
        $this->render('admin/users/adminMessages.html.twig', ['messages' => $messages]);
    }

    // Supprimer un message
    public function deleteAdminMessage(): void
    {
        if (!isset($_GET['id'])) {
            $_SESSION['error-message'] = "ID du message manquant.";
            $this->redirect("/AgriMai/index.php?route=list-AdminMessage");
        }

        $this->cm->deleteMessage((int)$_GET['id']);
        $_SESSION['success-message'] = "Message supprimé.";
        $this->redirect("/AgriMai/index.php?route=list-AdminMessage");
    }
}
