<?php

namespace App\Form;

use App\Document\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'placeholder' => 'Titre du post',
                    'class' => 'form-control'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'placeholder' => 'Contenu du post...',
                    'rows' => 10,
                    'class' => 'form-control'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
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
            ->add('pdfFile', FileType::class, [
                'label' => 'Document PDF',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                    ])
                ],
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'video/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                            'video/x-msvideo',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une vidéo valide (MP4, MPEG, MOV, AVI)',
                    ])
                ],
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'Audio/Musique',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'audio/*'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'audio/mpeg',
                            'audio/mp3',
                            'audio/wav',
                            'audio/ogg',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier audio valide (MP3, WAV, OGG)',
                    ])
                ],
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
            'data_class' => Post::class,
        ]);
    }
}
