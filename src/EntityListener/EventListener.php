<?php

namespace App\EntityListener;

use App\Entity\Event;
use Cocur\Slugify\Slugify;

class EventListener
{

    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    public function prePersist(Event $event)
    {
        $event->setUpdatedAt(new \DateTimeImmutable());
        $event->setSlug($this->slugify->slugify($event->getTitle()));
    }

    public function preUpdate(Event $event) {
        $event->setUpdatedAt(new \DateTimeImmutable());
        $event->setSlug($this->slugify->slugify($event->getTitle()));
    }

}