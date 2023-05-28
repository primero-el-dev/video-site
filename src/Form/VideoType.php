<?php

namespace App\Form;

use App\Entity\Video as VideoEntity;
use App\Form\DataTransformer\TagNamesToEntitiesTransformer;
use App\Validator\Image;
use App\Validator\Video as VideoValidator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class VideoType extends AbstractType
{
    public function __construct(private TagNamesToEntitiesTransformer $tagNamesToEntitiesTransformer) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $videoConstraints = [
            new VideoValidator(maxSize: '2048M'),
        ];
        if ($options['require_video']) {
            array_unshift($videoConstraints, new Assert\NotNull(message: 'form.video.video.notNull'));
        }
        
        $builder
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('snapshot', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Assert\NotNull(message: 'form.video.snapshot.notNull'),
                    new Image(maxSize: '2048M'),
                ],
            ])
            ->add('video', FileType::class, [
                'mapped' => false,
                'constraints' => $videoConstraints,
            ])
            ->add('snapshotTimestamp', NumberType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Range(min: 0, max: 9_999_999.999_999_999_999_999),
                ],
            ])
            ->add('sampleStartTimestamp', NumberType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Range(min: 0, max: 9_999_999.999_999_999_999_999),
                ],
            ])
            ->add('tags', TagCollectionType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VideoEntity::class,
            'csrf_protection' => false,
            'require_video' => true,
        ]);
    }
}
