<?php
namespace app\Enum;

enum UserStatus: string {
    case Pending = "En attente de validation";
    case Active  = "Actif";
    case Banned  = "Banni";
}
