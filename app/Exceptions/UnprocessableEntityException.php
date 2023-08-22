<?php

namespace App\Exceptions;

use Exception;

class UnprocessableEntityException extends Exception
{
    public function __construct($message = 'Unprocessable Entity', $code = 422)
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
