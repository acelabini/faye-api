<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class ApiException extends HttpResponseException
{
    public function __construct($message = null, $code = 500, array $details = [])
    {
        // build response object
        $response = new Response();

        $responseBody = [
            'error'  => [
                'code'    => $code,
                'message' => $message
            ]
        ];

        if (!empty($details)) {
            $responseBody['errors'] = $details;
        }

        $response->setContent($responseBody)
            ->setStatusCode($code);

        parent::__construct($response);
    }
}

