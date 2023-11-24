<?php

namespace App\EntityListener;

use App\Entity\User;

class UserListener
{

    public function prePersist(User $user)
    {
        $user->setUpdatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(User $user) {
     
        $user->setUpdatedAt(new \DateTimeImmutable());

    }

}