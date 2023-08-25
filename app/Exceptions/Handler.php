<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'Resource not found',
            ], 404);
        } elseif ($e instanceof BadRequestException) {
            return response()->json([
                'message' => $e->getMessage() ?? 'Bad Request',
                'status' => 'error',
                'code' => 400,
                'data' => json_decode($e->getMessage()),
            ], 400);
        } elseif ($e instanceof UnauthorizedException) {
            return response()->json([
                'message' => $e->getMessage() ?? 'Unauthorized',
                'status' => 'error',
                'code' => 401,
                'data' => json_decode($e->getMessage()),
            ], 401);
        } elseif ($e instanceof NotFoundException) {
            return response()->json([
                'message' => $e->getMessage() ?? 'Not Found',
                'status' => 'error',
                'code' => 404,
                'data' => json_decode($e->getMessage()),
            ], 404);
        } elseif ($e instanceof UnprocessableEntityException) {
            return response()->json([
                'message' => 'Unprocessable Entity',
                'status' => 'error',
                'code' => 422,
                'data' => json_decode($e->getMessage()),
            ], 422);
        } elseif ($e instanceof InternalServerErrorException) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        } elseif ($e instanceof RouteNotFoundException) {
            return response()->json([
                'message' => 'Unauthorized route, please login first',
                'status' => 'error',
                'code' => 401,
                'data' => json_decode($e->getMessage()),
            ], 401);
        }

        // return json
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}
