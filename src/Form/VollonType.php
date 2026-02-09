<?php

namespace App\Form;

use App\Entity\Vollon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VollonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'atelier',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Atelier d\'écriture créative']
            ])
            ->add('dateVollon', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control inline-datetime-field'],
                'required' => false,
                'row_attr' => ['class' => 'inline-datetime-group']
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control inline-datetime-field'],
                'required' => false,
                'row_attr' => ['class' => 'inline-datetime-group']
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control inline-datetime-field'],
                'required' => false,
                'help' => 'Indiquez l\'heure de fin de l\'atelier',
                'row_attr' => ['class' => 'inline-datetime-group']
            ])
            ->add('placesMax', IntegerType::class, [
                'label' => 'Nombre de places maximum',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 10',
                    'min' => 0
                ],
                'required' => false,
                'help' => 'Laissez vide pour un nombre de places illimité'
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
            ->add('isArchive', CheckboxType::class, [
                'label' => 'Archiver cet atelier',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vollon::class,
        ]);
    }
}
