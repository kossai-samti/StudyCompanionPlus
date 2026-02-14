<?php

namespace App\Form;

use App\Entity\Quiz;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Lesson;

class QuizType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lesson', EntityType::class, ['class' => Lesson::class, 'choice_label' => 'title'])
            ->add('difficulty', ChoiceType::class, ['choices' => ['Easy' => 'easy','Medium' => 'medium','Hard' => 'hard']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
