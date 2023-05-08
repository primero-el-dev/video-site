<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagType extends TextType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class);
        // $builder->get('name')->addModelTransformer(new CallbackTransformer(
        //     // 'strtolower',
        //     // 'strtolower',
        //     fn($tag) => strtolower($tag?->getName()),
        //     fn($name) => (new Tag())->setName(strtolower($name)),
        // ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
            'csrf_protection' => false,
        ]);
    }
}
