<?php

namespace App\Controller\OwnerDashboard;


use App\Controller\OwnerDashboard\UserCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Visit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/owner', routeName: 'owner')]
#[IsGranted('ROLE_OWNER')]
class OwnerDashboardController extends AbstractDashboardController
{

    public function index(): Response
    {

         $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
         return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Owner');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Strona główna', 'fa fa-home', '/');
        yield MenuItem::linkTo(UserCrudController::class, 'Użytkownicy', 'fa fa-images');
        yield MenuItem::linkTo(VisitCrudController::class, 'Odwiedzający', 'fa fa-images');

    }
}
