<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AvisReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reponse', TextareaType::class, [
                'label' => 'Votre réponse',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Rédigez votre réponse à cet avis...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Veuillez saisir une réponse'
                    ]),
                    new Assert\Length([
                        'min' => 0,
                        'max' => 1000,
                        'minMessage' => 'La réponse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La réponse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Si vous voulez lier directement à l'entité Avis
            // 'data_class' => Avis::class,
        ]);
    }
}
