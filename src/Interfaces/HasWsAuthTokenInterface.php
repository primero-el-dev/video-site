<?php

namespace App\Interfaces;

interface HasWsAuthTokenInterface
{
    public function setWsAuthTokenPath(string $wsAuthTokenPath): void;

    public function getWsAuthToken(): ?string;
}