<?php

namespace App\Form;

use App\Form\RepeatedPasswordType;
use App\Validator\CurrentUserPassword;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['old_password']) {
            $builder->add('oldPassword', PasswordType::class, [
                'constraints' => [
                    new CurrentUserPassword(),
                ],
            ]);
        }
        $builder->add('password', RepeatedPasswordType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'old_password' => false,
        ]);
    }
}
