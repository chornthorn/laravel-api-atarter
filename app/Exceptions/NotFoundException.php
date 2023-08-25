<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct($message = 'Not Found', $code = 404)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 404);
    }
}
