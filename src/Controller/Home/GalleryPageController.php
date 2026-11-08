<?php

namespace App\Controller\Home;

use App\Repository\GallerySessionRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryPageController extends AbstractController
{
    private const SESSIONS_PER_PAGE = 24;

    #[Route('/gallery', name: 'app_gallery')]
    public function index(
        GallerySessionRepository $repository,
        Request $request
    ): Response {
        $currentPage = max(1, $request->query->getInt('page', 1));

        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($currentPage - 1) * self::SESSIONS_PER_PAGE)
            ->setMaxResults(self::SESSIONS_PER_PAGE);

        $paginator = new Paginator($query, true);

        $totalSessions = count($paginator);
        $totalPages = max(1, (int) ceil($totalSessions / self::SESSIONS_PER_PAGE));

        // Jeśli ktoś otworzy stronę, która już nie istnieje,
        // przekieruj go na ostatnią dostępną stronę.
        if ($currentPage > $totalPages) {
            return $this->redirectToRoute('app_gallery', [
                'page' => $totalPages,
            ]);
        }

        $paginationStart = max(1, $currentPage - 2);
        $paginationEnd = min($totalPages, $currentPage + 2);

        return $this->render('pages/gallery.html.twig', [
            'sessions' => $paginator,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalSessions' => $totalSessions,
            'paginationPages' => range($paginationStart, $paginationEnd),
        ]);
    }

    #[Route('/gallery/{id}', name: 'app_gallery_show', requirements: ['id' => '\d+'])]
    public function show(
        int $id,
        GallerySessionRepository $repository
    ): Response {
        $session = $repository->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Nie znaleziono sesji zdjęciowej.');
        }

        return $this->render('pages/gallery-show.html.twig', [
            'session' => $session,
        ]);
    }
}
