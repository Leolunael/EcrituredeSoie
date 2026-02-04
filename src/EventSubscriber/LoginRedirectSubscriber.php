<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginRedirectSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // Vérifier les rôles et rediriger en conséquence
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // Les admins vont vers la page admin ou posts
            $targetUrl = $this->urlGenerator->generate('admin_index');
        } elseif (in_array('ROLE_PERMANENT', $user->getRoles(), true)) {
            // Les permanents vont vers les posts
            $targetUrl = $this->urlGenerator->generate('app_post_index');
        } else {
            // Les autres utilisateurs (ROLE_USER) vont vers la home
            $targetUrl = $this->urlGenerator->generate('app_home');
        }

        $response = new RedirectResponse($targetUrl);
        $event->setResponse($response);
    }
}
