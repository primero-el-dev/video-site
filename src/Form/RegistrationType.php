<?php

namespace App\Form;

use App\Entity\User;
use App\Form\ImageType;
use App\Validator\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;
use App\Form\RepeatedPasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nick', TextType::class)
            ->add('email', EmailType::class)
            ->add('password', RepeatedPasswordType::class, [
                'mapped' => false,
            ])
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_data' => '',
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Image(maxSize: '2048k'),
                ],
            ])
            ->add('background', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Image(maxSize: '2048k'),
                ],
            ])
            ->add('acceptRegulations', CheckboxType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new Assert\IsTrue(message: 'form.registration.acceptRegulations.isTrue'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}
