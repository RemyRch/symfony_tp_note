<?php

namespace App\Service;

use App\Entity\User;
use Twig\Environment;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AbstractService
{

    protected ?Request $request;

    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        protected EntityManagerInterface $em, 
        RequestStack $requestStack,
        private UrlGeneratorInterface $router,
        private Security $security,
        private Environment $twig,
        private UserPasswordHasherInterface $hasher,
        protected ParameterBagInterface $params
    ){
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function addFlash(string $type, string $message): void
    {
        $this->request->getSession()->getBag('flashes')->add($type, $message);
    }

    protected function redirect(string $url): RedirectResponse
    {
        return new RedirectResponse($url);
    }

    protected function redirectToRoute(string $route, array|null $parameters = null): RedirectResponse
    {
        return new RedirectResponse($this->router->generate($route, $parameters));
    }

    protected function getParameter(string $parameter)
    {
        return $this->params->get($parameter);
    }

    protected function render(string $view, array $parameters = [], Response $response = null) : Response
    {
        if (null === $response) $response = new Response();
        $response->setContent($this->twig->render($view, $parameters));
        return $response;
    }

    protected function getUser() : ?User
    {
        return $this->security->getUser();
    }

    protected function hashPassword(User $user, string $password): string
    {
        return $this->hasher->hashPassword($user, $password);
    }

    /**
     * Checks if the attribute is granted against the current authentication token and optionally supplied subject.
     *
     * @throws \LogicException
     */
    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        return $this->authorizationChecker->isGranted($attribute, $subject);
    }

}