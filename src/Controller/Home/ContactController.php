<?php

namespace App\Controller\Home;

use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/kontakt/wyslij', name: 'contact_send', methods: ['POST'])]
    public function send(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid('contact_form', $request->request->get('_token'))) {
            $this->addFlash('error', 'Nie udało się wysłać wiadomości. Spróbuj ponownie.');
            return $this->redirectToRoute('homepage');
        }

        $name = trim((string) $request->request->get('name'));
        $phone = trim((string) $request->request->get('phone'));
        $email = trim((string) $request->request->get('email'));
        $message = trim((string) $request->request->get('message'));

        if (
            $name === '' ||
            $phone === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            $message === ''
        ) {
            $this->addFlash('error', 'Wypełnij poprawnie wszystkie pola formularza.');
            return $this->redirectToRoute('homepage');
        }

        $contactMessage = new ContactMessage();
        $contactMessage
            ->setName($name)
            ->setPhone($phone)
            ->setEmail($email)
            ->setMessage($message)
            ->setIpAddress($request->getClientIp());

        $entityManager->persist($contactMessage);
        $entityManager->flush();

        $this->addFlash('success', 'Dziękujemy! Twoja wiadomość została wysłana.');

        return $this->redirectToRoute('homepage');
    }
}
