<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class CommonRuntime implements RuntimeExtensionInterface
{
    public function getEnvVariable(string $name): mixed
    {
        return $_ENV[$name];
    }
}
