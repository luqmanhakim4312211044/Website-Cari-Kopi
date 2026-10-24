<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Pembeli, penjual (pemilik cafe), atau admin web. */
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id || $user->id === $order->seller_id;
    }

    /** Penjual atau admin web boleh mengubah status pembayaran. */
    public function manage(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->seller_id;
    }
}
