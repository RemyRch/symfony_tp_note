<?php

namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em)
    {
        
    }

    #[Route(path: '/', name: 'app.home')]
    public function home(): Response
    {

        $events = $events = $this->em->getRepository(Event::class)->findWithPagination(null, 1, 5);

        return $this->render('home/index.html.twig', [
            "events" => $events
        ]);
    }
}
