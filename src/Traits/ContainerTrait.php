<?php

namespace App\Traits;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ContainerTrait
{
    protected ContainerInterface $container;

    // #[Required]
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }
}