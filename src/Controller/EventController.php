<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Event;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Form\EventFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em)
    {
        
    }

    #[Route(path: '/e/delete/{slug?}', name: 'events.delete')]
    public function delete(Event $event, Request $request): Response
    {
        
        /** @var User */
        $user = $this->getUser();

        $slug = $event->getCategory()->getSlug();

        if($user === $event->getAuthor() || $this->isGranted('ROLE_ADMIN')) {
            $this->em->remove($event);
            $this->em->flush();
        } else {
            throw new AccessDeniedHttpException('You are not allowed to delete this event.');
        }

        $this->addFlash('success', 'You\'re event has been deleted');
        return $this->redirect($this->generateUrl('events.index', ['slug' => $slug]));

    }

    #[Route(path: '/e/participate/{slug?}', name: 'events.participate')]
    public function participate(Event $event, Request $request): Response
    {
        
        $route = $request->headers->get('referer');

        $user = $this->getUser();
        if(!$user) return $this->redirect($route);
        if($user === $event->getAuthor()) return $this->redirect($route);

        $participation = $this->em->getRepository(Event::class)->checkParticipationDate($user, $event);
        if($participation) return $this->redirect($route);

        switch($event->getParticipants()->contains($this->getUser())){
            case true:
                $event->removeParticipant($this->getUser());
                $this->addFlash('success', 'You\'re not participing to this event anymore.');
                break;
            case false:
                $event->addParticipant($this->getUser());
                $this->addFlash('success', 'You\'re now participing to this event.');
                break;
        }

        $this->em->flush();

        return $this->redirect($route);

    }

    #[Route(path: '/event/{slug}', name: 'events.event')]
    public function event(Event $event, Request $request): Response
    {

        /** @var User */
        $user = $this->getUser();
        $route = $request->headers->get('referer');

        /** Comment form */
        $comment = new Comment();
        $newCommentForm = $this->createForm(CommentFormType::class, $comment);
        $newCommentForm->handleRequest($request);
        if($newCommentForm->isSubmitted() && $newCommentForm->isValid()){
            if(!$user) throw new AccessDeniedHttpException('You are not allowed to comment while you\'re not logged');
            $comment->setAuthor($user);
            $comment->setEvent($event);
            $this->addFlash('success', 'You\'re comment has been published');
            $this->em->persist($comment);
            $this->em->flush();
            return $this->redirect($route);
        }

        /** Edit event form */
        $eventForm = $this->createForm(EventFormType::class, $event);
        $eventForm->handleRequest($request);
        if($eventForm->isSubmitted() && $eventForm->isValid()){
        
            if($user != $event->getAuthor() && !$this->isGranted('ROLE_ADMIN')) throw new AccessDeniedHttpException('You are not allowed to edit this event');
            $this->addFlash('success', 'You\'re event has been updated');
            $this->em->persist($event);
            $this->em->flush();
            return $this->redirect($this->generateUrl('events.event', ['slug' => $event->getSlug()]));
        }

        return $this->render('events/show.html.twig', [
            "event" => $event,
            'newCommentForm' => $newCommentForm->createView(),
            'eventForm' => $eventForm->createView(),
        ]);
    }

    #[Route(path: '/my-participations', name: 'events.participations')]
    public function participations(Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();
        $events = $user->getParticipations();

        return $this->render('events/my-participations.html.twig', [
            "events" => $events
        ]);
    }

    #[Route(path: '/my-events', name: 'events.mine')]
    public function mine(Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();
        $events = $user->getEvents();

        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);
        $route = $request->headers->get('referer');

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $event->setAuthor($user);
            $this->addFlash('success', 'You\'re event has been published');
            $this->em->persist($event);
            $this->em->flush();
            return $this->redirect($route);
        }

        return $this->render('events/my-events.html.twig', [
            "events" => $events,
            'newEventForm' => $form->createView()
        ]);
    }

    #[Route(path: '/events/{slug?}', name: 'events.index')]
    public function events(?Category $category, Request $request): Response
    {
        if($category) {

            $page = $request->query->get('p');
            if($page != null && $page < 1) return $this->redirectToRoute('events.index', ['p' => 1, 'slug' => $category->getSlug()]);
            $maxPages = $this->em->getRepository(Event::class)->maxPages($category);

            $events = $this->em->getRepository(Event::class)->findWithPagination($category, 1);

            return $this->render('events/index.html.twig', [
                "events" => $events,
                "page" => $page ?? 1,
                "category" => $category,
                "pages" => $maxPages
            ]);
        } else {
            $categories = $this->em->getRepository(Category::class)->findAll();
            return $this->render('events/categories.html.twig', [
                "categories" => $categories,
            ]);
        }
        
    }
}
