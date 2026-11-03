<?php

namespace App\Controller\Home;

use App\Repository\GallerySessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryPageController extends AbstractController
{
    #[Route('/gallery', name: 'app_gallery')]
    public function index(GallerySessionRepository $repository): Response
    {
        $sessions = $repository->findBy([], ['id' => 'DESC']);

        return $this->render('pages/gallery.html.twig', [
            'sessions' => $sessions,
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
