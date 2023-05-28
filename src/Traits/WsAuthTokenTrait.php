<?php

namespace App\Traits;

use Symfony\Contracts\Service\Attribute\Required;

trait WsAuthTokenTrait
{
    protected string $wsAuthTokenPath;

    #[Required]
    public function setWsAuthTokenPath(string $wsAuthTokenPath): void
    {
        $this->wsAuthTokenPath = $wsAuthTokenPath;
    }

    public function getWsAuthToken(): ?string
    {
        return file_get_contents($this->wsAuthTokenPath);
    }
}