<?php

namespace App\Form;

use App\Form\DataTransformer\TagNamesToEntitiesTransformer;
use App\Validator\Tag;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TagCollectionType extends CollectionType
{
    public function __construct(private TagNamesToEntitiesTransformer $tagNamesToEntitiesTransformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->addModelTransformer($this->tagNamesToEntitiesTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'entry_type' => TextType::class,
            'csrf_protection' => false,
            'mapped' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'constraints' => [
                new Assert\Type('countable'),
                new Assert\Count(max: 15, maxMessage: 'form.tagCollection.count.max'),
                new Assert\All([
                    new Assert\NotBlank(),
                    new Assert\Type(type: 'object'),
                    new Tag(message: 'form.tagCollection.tag', maxLength: 140),
                ]),
            ],
        ]);
    }
}
