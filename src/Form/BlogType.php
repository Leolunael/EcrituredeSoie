<?php

namespace App\Form;

use App\Document\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre du blog',
                    'class' => 'form-control'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Contenu du blog...',
                    'rows' => 15,
                    'class' => 'form-control'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF ou WebP)',
                    ])
                ],
            ])
            ->add('imageAlignment', ChoiceType::class, [
                'label' => 'Position de l\'image',
                'choices' => [
                    'À gauche' => 'left',
                    'À droite' => 'right',
                ],
                'expanded' => true, // Affiche des boutons radio
                'required' => false,
            ])
            ->add('aLaUne', CheckboxType::class, [
                'label' => 'Mettre à la une',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('publie', CheckboxType::class, [
                'label' => 'Publié',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
