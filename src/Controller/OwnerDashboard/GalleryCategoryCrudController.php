<?php

namespace App\Controller\OwnerDashboard;

use App\Entity\GalleryCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class GalleryCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GalleryCategory::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nazwa kategorii')
                ->setRequired(true)
                ->setMaxLength(100),
        ];
    }
}
