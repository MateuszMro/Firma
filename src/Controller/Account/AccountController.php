<?php

namespace App\Controller\Account;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    #[Route('/my-account', name: 'account_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        /** @var User $account */
        $account = $this->getUser();

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_token');
            $password = $request->request->get('password');

            if (!$csrfTokenManager->isTokenValid(
                new CsrfToken('delete_account', $token)
            )) {
                throw $this->createAccessDeniedException('Nieprawidłowy token bezpieczeństwa.');
            }

            if (!$passwordHasher->isPasswordValid($account, $password)) {
                $this->addFlash('danger', 'Podane hasło jest nieprawidłowe.');

                return $this->redirectToRoute('account_index');
            }

            $entityManager->remove($account);
            $entityManager->flush();

            $this->addFlash('success', 'Twoje konto zostało usunięte.');

            $tokenStorage->setToken(null);

            if ($request->hasSession()) {
                $request->getSession()->invalidate();
            }

            return $this->redirectToRoute('homepage');
        }

        return $this->render('account/index.html.twig', [
            'account' => $account,
        ]);
    }
}
