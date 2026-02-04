<?php

namespace App\Form;

use App\Entity\PermPresentation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermPresentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre principal',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Rejoignez nos Ateliers']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Décrivez l\'espace Ateliers...'
                ]
            ])
            ->add('avantages', TextareaType::class, [
                'label' => 'Avantages (un par ligne)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 8,
                    'placeholder' => "Un avantage par ligne:\nAccès aux ateliers\nPartage de contenus\netc."
                ],
                'help' => 'Entrez un avantage par ligne'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PermPresentation::class,
        ]);
    }
}
