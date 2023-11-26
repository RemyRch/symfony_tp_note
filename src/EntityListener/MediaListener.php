<?php

namespace App\EntityListener;

use App\Entity\Media;
use App\Service\AbstractService;

class MediaListener extends AbstractService
{

    public function prePersist(Media $media)
    {
        
        $media->setCreatedAt(new \DateTimeImmutable());
        if($media->getMediaFile() != null){
            $media->setExtension($media->getMediaFile()->guessExtension());
            $media->setSize($media->getMediaFile()->getSize());
        }

    }

    public function preRemove(Media $media)
    {
        if(file_exists($this->getParameter('kernel.project_dir') . '/public/' . $media->getPath())){
            unlink($this->getParameter('kernel.project_dir') . '/public/' . $media->getPath());
        }
    }
}