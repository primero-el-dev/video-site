<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;

class RepeatedPasswordType extends RepeatedType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'csrf_protection' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'form.repeatedPassword.match',
            'mapped' => true,
            'constraints' => [
                new Assert\NotBlank(message: 'form.repeatedPassword.notBlank'),
                new Assert\Length(
                    min: 12, 
                    max: 50, 
                    minMessage: 'form.repeatedPassword.length.min', 
                    maxMessage: 'form.repeatedPassword.length.max'
                ),
                new Assert\Regex(
                    pattern: '/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*$/', 
                    message: 'form.repeatedPassword.regex'
                ),
            ],
        ]);
    }
}
