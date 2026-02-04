<?php

namespace App\EventListener;

use App\Service\VisitorTrackerService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
class VisitorTrackingListener
{
    public function __construct(
        private VisitorTrackerService $visitorTracker
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Uniquement pour les requêtes principales (pas les sous-requêtes)
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        try {
            $this->visitorTracker->trackVisit($request);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas interrompre la requête
            // Vous pouvez utiliser le logger ici si besoin
        }
    }
}
