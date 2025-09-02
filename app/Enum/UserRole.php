<?php
// Enum pour le rôle d'un utilisateur
namespace app\Enum;

enum UserRole: string {
    case Buyer = "Acheteur";
    case Producer = "Producteur";
}