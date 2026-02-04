<?php

namespace App\Service;

use App\Entity\Visite;
use App\Repository\VisiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class VisitorTrackerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private VisiteRepository $visiteRepository
    ) {
    }

    /**
     * Enregistre une visite
     */
    public function trackVisit(Request $request): void
    {
        // Exclure les routes admin et les assets
        $route = $request->attributes->get('_route');
        if ($this->shouldExclude($route, $request->getPathInfo())) {
            return;
        }

        $ip = $this->getClientIp($request);

        // Optionnel : ne compter qu'une visite par IP par jour
        // Décommentez la ligne suivante si vous voulez cette fonctionnalité
        // if ($this->visiteRepository->hasVisitedToday($ip)) return;

        $visite = new Visite();
        $visite->setIpAddress($ip);
        $visite->setUserAgent($request->headers->get('User-Agent'));
        $visite->setUrl($request->getUri());
        $visite->setReferer($request->headers->get('referer'));
        $visite->setSessionId($request->getSession()?->getId());

        $this->entityManager->persist($visite);
        $this->entityManager->flush();
    }

    /**
     * Obtient l'IP réelle du client
     */
    private function getClientIp(Request $request): string
    {
        $ipAddress = $request->getClientIp();

        // Si derrière un proxy/load balancer
        if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
            $ipList = explode(',', $request->server->get('HTTP_X_FORWARDED_FOR'));
            $ipAddress = trim($ipList[0]);
        }

        return $ipAddress ?? '0.0.0.0';
    }

    /**
     * Détermine si la route doit être exclue du tracking
     */
    private function shouldExclude(?string $route, string $path): bool
    {
        // Exclure les routes admin
        if ($route && str_starts_with($route, 'admin_')) {
            return true;
        }

        // Exclure les assets, API, etc.
        $excludedPaths = [
            '/admin',
            '/api',
            '/_profiler',
            '/_wdt',
            '/bundles',
            '/build',
            '/assets',
            '/favicon.ico',
            '/robots.txt',
        ];

        foreach ($excludedPaths as $excludedPath) {
            if (str_starts_with($path, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtient toutes les statistiques
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->visiteRepository->countTotal(),
            'uniqueVisitors' => $this->visiteRepository->countUniqueVisitors(),
            'today' => $this->visiteRepository->countToday(),
            'uniqueToday' => $this->visiteRepository->countUniqueTodayVisitors(),
            'thisWeek' => $this->visiteRepository->countThisWeek(),
            'thisMonth' => $this->visiteRepository->countThisMonth(),
            'statsByDay' => $this->visiteRepository->getStatsByDay(7),
            'topPages' => $this->visiteRepository->getTopPages(5),
            'recentVisites' => $this->visiteRepository->getRecentVisites(10),
        ];
    }
}
