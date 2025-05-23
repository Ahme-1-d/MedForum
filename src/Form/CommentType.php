<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Article;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_medecin', TextType::class, [
                'label' => 'Nom du médecin',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du médecin'
                ]
            ])
            ->add('nom_patient', TextType::class, [
                'label' => 'Nom du patient',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du patient'
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                    'placeholder' => 'Écrivez votre commentaire ici...'
                ]
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices' => [
                    '1 étoile' => 1,
                    '2 étoiles' => 2,
                    '3 étoiles' => 3,
                    '4 étoiles' => 4,
                    '5 étoiles' => 5,
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'titre',
                'label' => 'Article',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
