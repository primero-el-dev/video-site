<?php

namespace App\EventListener;

use App\Entity\Tag;
use App\Entity\Video;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TagListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Video) {
            return;
        }

        $em = $args->getObjectManager();
        $allNames = $entity->getTags()->map(fn($t) => $t->getName())->toArray();
        $existingTags = $em->getRepository(Tag::class)->findByName($allNames);
        $existingNames = array_map(fn($t) => $t->getName(), $existingTags);
        $missingTags = $entity
            ->getTags()
            ->filter(fn($t) => !in_array($t->getName(), $existingNames));

        // foreach ($missingTags as $tag) {
        //     $em->persist($tag);
        // }
        // foreach ($existingTags as $tag) {
        //     $em->detach($tag);
        // }
        // $em->flush();
    }
}