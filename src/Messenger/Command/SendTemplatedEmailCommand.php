<?php

namespace App\Messenger\Command;

use App\Messenger\AsyncInterface;
use App\Messenger\Command\CommandInterface;

class SendTemplatedEmailCommand implements CommandInterface, AsyncInterface
{
    public function __construct(
        /** @param int[] */
        public readonly array $userIds,
        /** @param string | [string, array] $subject - subject or array for translation in form [translationKey, params] */
        public readonly string|array $subject,
        public readonly string $templatePath,
        public readonly bool $secondaryEmail = false,
        /** @param mixed[] */
        public readonly array $additionalParams = [],
    ) {}
}