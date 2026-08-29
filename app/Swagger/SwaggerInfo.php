<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    description: "API documentation for the Wartil platform.",
    title: "Wartil API Documentation",
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "Wartil API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class SwaggerInfo
{
}
