<?php

namespace App\Controller\EventSubscriber;

use App\Entity\Visit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class VisitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

//        // Liczymy tylko wejścia na publiczne podstrony metodą GET.
//        if ($request->getMethod() !== 'GET') {
//            return;
//        }
//
//        if (!$request->attributes->get('_route')) {
//            return;
//        }
        // Liczymy wyłącznie wejścia na stronę główną.
        if ($request->getMethod() !== 'GET' || $request->attributes->get('_route') !== 'homepage') {
            return;
        }

        // Nie liczymy panelu właściciela ani narzędzi Symfony.
        if (
            str_starts_with($request->getPathInfo(), '/owner')
            || str_starts_with($request->getPathInfo(), '/_')
        ) {
            return;
        }

        if ($response->getStatusCode() >= 400) {
            return;
        }

        // Identyfikator przeglądarki.
        $visitorId = $request->cookies->get('site_visitor');

        if (!$visitorId || !preg_match('/^[a-f0-9]{64}$/', $visitorId)) {
            $visitorId = bin2hex(random_bytes(32));
        }

        // Zapisujemy każde wejście.
        $visit = new Visit();
        $visit->setVisitorId($visitorId);
        $visit->setIpAdress(
            $request->getClientIp() ?? 'unknown'
        );
        $visit->setVisitedAt(new \DateTimeImmutable());

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        // Ciasteczko pozwala rozpoznać powracającą przeglądarkę.
        $response->headers->setCookie(
            Cookie::create('site_visitor')
                ->withValue($visitorId)
                ->withExpires(new \DateTimeImmutable('+1 year'))
                ->withPath('/')
                ->withSecure($request->isSecure())
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_LAX)
        );
    }
}
