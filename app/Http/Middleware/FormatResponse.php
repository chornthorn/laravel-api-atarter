<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormatResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the response status is either 200 or 201
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
            // Set the base response for successful requests
            $data = [
                'message' => 'Request processed successfully',
                'status' => 'success',
                'code' => $response->getStatusCode(),
                'data' => json_decode($response->getContent()),
            ];

            return response()->json($data);
        }

        return $response;
    }
}
