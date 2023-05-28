<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Request;
use App\Exception\ApiException;

trait GetJsonContentOrThrowApiException
{
    /**
     * @throws ApiException
     * @return mixed[]
     */
    protected function getJsonContentOrThrowApiException(Request $request): ?array
    {
        $content = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Invalid JSON content.', 400);
        }

        return $content;
    }
}