<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\CommonRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CommonExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_env', [CommonRuntime::class, 'getEnvVariable']),
        ];
    }
}
