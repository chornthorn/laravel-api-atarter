<?php

namespace App\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public function __construct($message = 'Unauthorized', $code = 401)
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
