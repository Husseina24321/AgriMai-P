<?php
namespace services;
use Exception;
class CsrfTokenManager
{
    public function generateCSRFToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $token = bin2hex(random_bytes(32));
            $_SESSION['csrf-token'] = $token;
            return $token;
        } catch (Exception $e) {
            error_log("Erreur CsrfTokenManager : " . $e->getMessage());
            die("Erreur lors de la génération du token CSRF");
        }
    }

    public function validateCSRFToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['csrf-token']) && hash_equals($_SESSION['csrf-token'], $token);
    }
}
