<?php

namespace App\Controller\Admin;

use App\Entity\GallerySession;
use App\Entity\GalleryImage;
use App\Form\GallerySessionType;
use App\Repository\GallerySessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/owner/gallery')]
#[IsGranted('ROLE_OWNER')]
class GalleryAdminController extends AbstractController
{
    #[Route('', name: 'admin_gallery_admin_panel_gallery', methods: ['GET'])]
    public function index(
        GallerySessionRepository $repository,
        Request $request
    ): Response {
        $sessionsPerPage = 24;
        $currentPage = max(1, $request->query->getInt('page', 1));

        $query = $repository->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($currentPage - 1) * $sessionsPerPage)
            ->setMaxResults($sessionsPerPage);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, true);

        $totalSessions = count($paginator);
        $totalPages = max(1, (int) ceil($totalSessions / $sessionsPerPage));

        if ($currentPage > $totalPages) {
            return $this->redirectToRoute('admin_gallery_admin_panel_gallery', [
                'page' => $totalPages,
            ]);
        }

        $paginationStart = max(1, $currentPage - 2);
        $paginationEnd = min($totalPages, $currentPage + 2);

        return $this->render('admin/gallery/admin-panel-gallery.html.twig', [
            'sessions' => $paginator,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalSessions' => $totalSessions,
            'paginationPages' => range($paginationStart, $paginationEnd),
        ]);
    }

    #[Route('/add', name: 'admin_gallery_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $session = new GallerySession();
        $session->setCreatedAt(date('d-m-Y'));

        $form = $this->createForm(GallerySessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFiles = $form->get('images')->getData();

            $uploadDirectory = $this->getParameter('kernel.project_dir')
                . '/public/uploads/gallery';

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0775, true);
            }

            foreach ($uploadedFiles ?? [] as $uploadedFile) {
                $originalName = pathinfo(
                    $uploadedFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $safeName = $slugger->slug($originalName);
                $newFilename = $safeName . '-' . bin2hex(random_bytes(6)) . '.jpg';

                $sourceImage = imagecreatefromstring(
                    file_get_contents($uploadedFile->getPathname())
                );

                if (!$sourceImage) {
                    throw new \RuntimeException('Nie udało się odczytać zdjęcia.');
                }

                $sourceImage = $this->fixImageOrientation(
                    $sourceImage,
                    $uploadedFile->getPathname()
                );

                imagejpeg(
                    $sourceImage,
                    $uploadDirectory . '/' . $newFilename,
                    90
                );

                imagedestroy($sourceImage);

                $image = new GalleryImage();
                $image->setFilename($newFilename);

                $session->addImage($image);
            }

            $entityManager->persist($session);
            $entityManager->flush();

            $this->addFlash('success', 'Zdjęcia zostały dodane.');

            return $this->redirectToRoute('admin_gallery_add');
        }

        return $this->render('admin/gallery/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_gallery_edit', requirements: ['id' => '\d+'])]
    public function edit(
        int $id,
        Request $request,
        GallerySessionRepository $repository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $session = $repository->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Nie znaleziono sesji.');
        }

        $form = $this->createForm(GallerySessionType::class, $session, [
            'edit_mode' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFiles = $form->get('images')->getData();

            $uploadDirectory = $this->getParameter('kernel.project_dir')
                . '/public/uploads/gallery';

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0775, true);
            }

            foreach ($uploadedFiles ?? [] as $uploadedFile) {
                $originalName = pathinfo(
                    $uploadedFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );

                $safeName = $slugger->slug($originalName);
                $newFilename = $safeName . '-' . bin2hex(random_bytes(6)) . '.jpg';

                $sourceImage = imagecreatefromstring(
                    file_get_contents($uploadedFile->getPathname())
                );

                if (!$sourceImage) {
                    throw new \RuntimeException('Nie udało się odczytać zdjęcia.');
                }

                $sourceImage = $this->fixImageOrientation(
                    $sourceImage,
                    $uploadedFile->getPathname()
                );

                imagejpeg(
                    $sourceImage,
                    $uploadDirectory . '/' . $newFilename,
                    90
                );

                imagedestroy($sourceImage);

                $image = new GalleryImage();
                $image->setFilename($newFilename);

                $session->addImage($image);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Zmiany zostały zapisane.');

            return $this->redirectToRoute('admin_gallery_edit', [
                'id' => $session->getId(),
            ]);
        }

        return $this->render('admin/gallery/edit.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/images/delete/{id}',
        name: 'admin_gallery_image_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function deleteImage(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        \App\Repository\GalleryImageRepository $imageRepository
    ): Response {
        $image = $imageRepository->find($id);

        if (!$image) {
            throw $this->createNotFoundException('Nie znaleziono zdjęcia.');
        }

        if (!$this->isCsrfTokenValid(
            'delete_image_' . $image->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Nieprawidłowy token formularza.');
        }

        $filename = $image->getFilename();
        $sessionId = $image->getGallerySession()->getId();

        $entityManager->remove($image);
        $entityManager->flush();

        $filePath = $this->getParameter('kernel.project_dir')
            . '/public/uploads/gallery/' . $filename;

        if (is_file($filePath)) {
            unlink($filePath);
        }

        $this->addFlash('success', 'Zdjęcie zostało usunięte.');

        return $this->redirectToRoute('admin_gallery_edit', [
            'id' => $sessionId,
        ]);
    }

    #[Route('/delete/{id}',
        name: 'admin_gallery_delete',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    public function delete(
        int $id,
        Request $request,
        GallerySessionRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $repository->find($id);

        if (!$session) {
            throw $this->createNotFoundException('Nie znaleziono sesji.');
        }

        if (!$this->isCsrfTokenValid(
            'delete_session_' . $session->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Nieprawidłowy token formularza.');
        }

        $uploadDirectory = $this->getParameter('kernel.project_dir')
            . '/public/uploads/gallery';

        foreach ($session->getImages() as $image) {
            $filePath = $uploadDirectory . '/' . $image->getFilename();

            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        $entityManager->remove($session);
        $entityManager->flush();

        $this->addFlash('success', 'Wszystkie zdjęcia zostały usunięte.');

        return $this->redirectToRoute('admin_gallery_admin_panel_gallery');
    }

    private function fixImageOrientation(
        \GdImage $image,
        string $filePath
    ): \GdImage {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($filePath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        switch ($orientation) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;

            case 3:
                $image = imagerotate($image, 180, 0) ?: $image;
                break;

            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;

            case 5:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = imagerotate($image, 90, 0) ?: $image;
                break;

            case 6:
                $image = imagerotate($image, -90, 0) ?: $image;
                break;

            case 7:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = imagerotate($image, -90, 0) ?: $image;
                break;

            case 8:
                $image = imagerotate($image, 90, 0) ?: $image;
                break;
        }

        return $image;
    }
}
