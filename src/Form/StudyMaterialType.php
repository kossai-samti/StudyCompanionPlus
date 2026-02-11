<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\StudyMaterial;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudyMaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('content')
            ->add('summary')
            ->add('flashcards')
            ->add('lesson', EntityType::class, [
                'class' => Lesson::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    $resolver->setDefaults([
        'data_class' => StudyMaterial::class,
        'attr' => ['novalidate' => 'novalidate'] // This disables browser validation
    ]);
    }
}
