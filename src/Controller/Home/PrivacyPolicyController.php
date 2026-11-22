<?php

namespace App\Controller\Home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrivacyPolicyController extends AbstractController
{
    #[Route('/polityka-prywatnosci', name: 'app_privacy_policy')]
    public function index(): Response
    {
        return $this->render('pages/privacy-policy.html.twig');
    }
}
