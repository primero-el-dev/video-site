<?php

namespace App\Exception;

use Exception;
use Throwable;

class ApiException extends Exception
{
    public function __construct(string $message = '', $code = 500, Throwable $previous = null, protected array $data = [])
    {
        parent::__construct($message, $code, $previous);
    }

    public function presentData(): array
    {
        $allData = $this->data;
        if ($this->message) {
            $allData['error'] = $this->message;
        }
        
        return $allData;
    }
}