<?php

namespace App\Form;

use App\Entity\Presentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PresentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ex: Agnès – Animatrice d\'ateliers d\'écriture'
                ]
            ])
            ->add('sousTitre', TextType::class, [
                'label' => 'SousTitre',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'ex: EHPAD'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'placeholder' => 'Votre contenu ici...'
                ]
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Ordre d\'affichage sur la page (0 = premier, 1 = deuxième, etc.)'
            ])

            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'help' => 'Décochez pour masquer ce contenu sans le supprimer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Presentation::class,
        ]);
    }
}
