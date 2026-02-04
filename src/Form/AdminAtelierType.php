<?php

namespace App\Form;

use App\Entity\Atelier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AdminAtelierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'atelier',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Atelier d\'écriture créative']
            ])
            ->add('dateAtelier', DateTimeType::class, [
                'label' => 'Date et heure de l\'atelier',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Café des Arts, 12 rue de la Paix'],
                'required' => false
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
                'label' => 'Atelier archivé',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Atelier::class,
        ]);
    }
}
