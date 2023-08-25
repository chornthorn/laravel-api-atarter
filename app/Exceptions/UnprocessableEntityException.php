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
        // array of errors
        $errors = [
            'message' => $this->getMessage(),
        ];

        return response()->json($errors, 422);
    }
}
