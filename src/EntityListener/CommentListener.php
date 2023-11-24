<?php

namespace App\EntityListener;

use App\Entity\Comment;

class CommentListener
{

    public function prePersist(Comment $comment)
    {
        $comment->setCreatedAt(new \DateTimeImmutable());
    }

    public function preUpdate(Comment $comment) {
        $comment->setCreatedAt(new \DateTimeImmutable());
    }

}