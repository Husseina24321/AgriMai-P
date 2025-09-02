<?php
namespace app\Enum;

enum OrderStatus: string
{
    case Pending   = 'En attente';     // Commande créée mais pas encore confirmée
    case Confirmed = 'confirmée';   // Commande validée par le vendeur
    case Shipped   = 'Expédiée';     // Commande expédiée
   // case Delivered = 'delivered';   // Commande reçue par le client
   // case Cancelled = 'cancelled';   // Commande annulée
   // case Refunded  = 'refunded';    // Commande remboursée
}
