<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: "1.0.0",
        title: "My API Documentation",
        description: "Laravel 12 API Documentation with Swagger (OpenAPI 3)"
    ),

    OA\Server(
        url: "http://127.0.0.1:8000/api",
        description: "Local Server"
    ),
    OA\Server(
        url: "https://staging.yourdomain.com/api",
        description: "Staging Server"
    ),
    OA\Server(
        url: "https://productiondomain.com/api",
        description: "Production Server"
    ),
]

abstract class Controller
{
    //
}
