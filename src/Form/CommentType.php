<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Video;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class);

        if (!$options['update_mode']) {
            $builder
                ->add('video', EntityType::class, [
                    'class' => Video::class,
                ])
                ->add('parent', EntityType::class, [
                    'class' => Comment::class,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'csrf_protection' => false,
            'update_mode' => false,
        ]);
    }
}
