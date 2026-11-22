<?php

namespace App\Controller\Home;

use App\Repository\GallerySessionRepository;
use App\Repository\GalleryCategoryRepository;
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
        GalleryCategoryRepository $categoryRepository,
        GallerySessionRepository $repository,
        Request $request
    ): Response {
        $currentPage = max(1, $request->query->getInt('page', 1));
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);

        $selectedCategoryId = $request->query->getInt('category', 0);

        $selectedCategory = $selectedCategoryId > 0
            ? $categoryRepository->find($selectedCategoryId)
            : null;

        if ($selectedCategoryId > 0 && !$selectedCategory) {
            return $this->redirectToRoute('app_gallery');
        }

        $queryBuilder = $repository->createQueryBuilder('s');

        if ($selectedCategory !== null) {
            $queryBuilder
                ->innerJoin('s.categories', 'c')
                ->andWhere('c.id = :categoryId')
                ->setParameter('categoryId', $selectedCategory->getId())
                ->distinct();
        }

        $query = $queryBuilder
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($currentPage - 1) * self::SESSIONS_PER_PAGE)
            ->setMaxResults(self::SESSIONS_PER_PAGE);

        $paginator = new Paginator($query, true);

        $totalSessions = count($paginator);
        $totalPages = max(1, (int) ceil($totalSessions / self::SESSIONS_PER_PAGE));

        if ($currentPage > $totalPages) {
            return $this->redirectToRoute('app_gallery', [
                'page' => $totalPages,
                'category' => $selectedCategoryId ?: null,
            ]);
        }

        $paginationStart = max(1, $currentPage - 2);
        $paginationEnd = min($totalPages, $currentPage + 2);

        $template = $request->isXmlHttpRequest()
            ? 'pages/gallery_results.html.twig'
            : 'pages/gallery.html.twig';

        return $this->render($template, [
            'sessions' => $paginator,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalSessions' => $totalSessions,
            'paginationPages' => range($paginationStart, $paginationEnd),
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'selectedCategoryId' => $selectedCategory?->getId(),
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
