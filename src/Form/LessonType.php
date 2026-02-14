<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('subject', TextType::class)
            ->add('difficulty', ChoiceType::class, [
                'choices' => ['Easy' => 'easy','Medium' => 'medium','Hard' => 'hard']
            ])
            ->add('file_path', TextType::class, ['required' => false])
        ;

        if ($options['is_teacher']) {
            $builder->add('target_group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => 'Select target group',
                'label' => 'Target Group',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'is_teacher' => false,
        ]);
    }
}
