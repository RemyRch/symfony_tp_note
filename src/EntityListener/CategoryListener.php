<?php

namespace App\EntityListener;

use App\Entity\Category;
use Cocur\Slugify\Slugify;

class CategoryListener
{

    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    public function prePersist(Category $category)
    {
        $category->setSlug($this->slugify->slugify($category->getTitle()));
    }

    public function preUpdate(Category $category) 
    {
        $category->setSlug($this->slugify->slugify($category->getTitle()));
    }

}