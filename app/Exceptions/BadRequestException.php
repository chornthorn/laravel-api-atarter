<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct($message = 'Bad Request', $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->getCode(),
            ]
        ], $this->getCode());
    }
}
