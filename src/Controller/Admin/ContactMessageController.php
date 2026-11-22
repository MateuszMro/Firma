<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactMessageController extends AbstractController
{
    #[Route('/owner/messages', name: 'admin_messages', methods: ['GET'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_OWNER');

        $repository = $entityManager->getRepository(ContactMessage::class);

        $messages = $repository->findBy([], ['createdAt' => 'DESC']);

        $selectedMessage = null;
        $selectedId = $request->query->get('id');

        if ($selectedId !== null) {
            $selectedMessage = $repository->find($selectedId);

            if (
                $selectedMessage
                && $selectedMessage->getStatus() === ContactMessage::STATUS_NEW
            ) {
                $selectedMessage->setStatus(ContactMessage::STATUS_READ);
                $entityManager->flush();
            }
        }

        $unreadCount = count(array_filter(
            $messages,
            fn (ContactMessage $message) =>
                $message->getStatus() === ContactMessage::STATUS_NEW
        ));

        return $this->render('admin/message/messages.html.twig', [
            'messages' => $messages,
            'selectedMessage' => $selectedMessage,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route(
        '/owner/messages/{id}/podglad',
        name: 'admin_message_show',
        methods: ['GET']
    )]
    public function show(
        ContactMessage $message,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_OWNER');

        if ($message->getStatus() === ContactMessage::STATUS_NEW) {
            $message->setStatus(ContactMessage::STATUS_READ);
            $entityManager->flush();
        }

        $unreadCount = $entityManager
            ->getRepository(ContactMessage::class)
            ->count(['status' => ContactMessage::STATUS_NEW]);

        $html = $this->renderView('admin/message/message_content.html.twig', [
            'selectedMessage' => $message,
        ]);

        return $this->json([
            'html' => $html,
            'unreadCount' => $unreadCount,
        ]);
    }

    #[Route(
        '/owner/messages/{id}/usun',
        name: 'admin_message_delete',
        methods: ['POST']
    )]
    public function delete(
        ContactMessage $message,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_OWNER');

        if (!$this->isCsrfTokenValid(
            'delete_message_' . $message->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException(
                'Nieprawidłowy token bezpieczeństwa.'
            );
        }

        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Wiadomość została usunięta.');

        return $this->redirectToRoute('admin_messages');
    }
}
