<?php

namespace App\Controller\OwnerDashboard;

use App\Entity\ContactMessage;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ContactMessage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('name', 'Imię i nazwisko'),
            TextField::new('phone', 'Telefon'),
            TextField::new('email', 'E-mail'),
            TextareaField::new('message', 'Treść wiadomości'),
            TextField::new('status', 'Status'),
            DateTimeField::new('createdAt', 'Data otrzymania'),
            TextField::new('ipAddress', 'Adres IP')
        ];
    }
}
