<?php

namespace App\Form;

use App\Entity\Lettre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminLettreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Lettre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Lettre']
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Description détaillée de l\'atelier...'
                ]
            ])
            ->add('informations', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Informations pratiques, matériel à apporter, etc.'
                ],
                'required' => false
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix (€)',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control', 'placeholder' => '25.00'],
                'required' => false
            ])
            ->add('lienHelloAsso', UrlType::class, [
                'label' => 'Lien HelloAsso',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://www.helloasso.com/...'
                ],
                'required' => false,
                'help' => 'URL complète du formulaire de paiement HelloAsso'
            ])
            ->add('archive', CheckboxType::class, [
                'label' => 'Lettre archivée',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lettre::class,
        ]);
    }
}
