<?php

namespace App\Form\DataTransformer;

use App\Entity\Tag;
use App\Traits\EntityManagerTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class TagNamesToEntitiesTransformer implements DataTransformerInterface
{
    use EntityManagerTrait;

    public function transform($tags)
    {
        return $tags->map(fn($t) => $t->getName())->toArray();
    }

    public function reverseTransform($tagNames)
    {
        $tags = new ArrayCollection();
        $selectedTags = $this->em
            ->getRepository(Tag::class)
            ->findByName($tagNames);
        
        foreach ($tagNames as $tagName) {
            $tag = array_filter($selectedTags, fn($t) => $t->getName() === strtolower($tagName))[0] 
                ?? (new Tag())->setName($tagName);
            $tags->add($tag);
        }

        return $tags;
    }
}