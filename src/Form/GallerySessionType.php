<?php

namespace App\Form;

use App\Entity\GallerySession;
use App\Entity\GalleryCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GallerySessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $editMode = $options['edit_mode'];

        $imageConstraints = [
            new Assert\All([
                new Assert\Image(
                    maxSize: '500M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                    mimeTypesMessage: 'Dozwolone są pliki JPG, PNG i WEBP.'
                ),
            ]),
        ];

        if (!$editMode) {
            array_unshift($imageConstraints, new Assert\Count(
                min: 1,
                minMessage: 'Dodaj przynajmniej jedno zdjęcie.'
            ));
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Tytuł',
                'attr' => [
                    'placeholder' => 'Tytuł opisujący produkt',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'attr' => [
                    'placeholder' => 'Opisz prezentowany produkt...',
                    'rows' => 5,
                ],
            ])
            ->add('categories', EntityType::class, [
                'class' => GalleryCategory::class,
                'choice_label' => 'name',
                'label' => 'Kategorie',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('images', FileType::class, [
                'label' => $editMode ? 'Dodaj kolejne zdjęcia' : 'Zdjęcia',
                'mapped' => false,
                'multiple' => true,
                'required' => !$editMode,
                'constraints' => $imageConstraints,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'multiple' => true,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GallerySession::class,
            'edit_mode' => false,
        ]);

        $resolver->setAllowedTypes('edit_mode', 'bool');
    }
}
