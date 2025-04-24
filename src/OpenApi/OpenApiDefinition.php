<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API документация',
    version: '1.0.0',
    description: 'Документация к API study-on.billing'
)]
#[OA\SecurityScheme(
    securityScheme: 'Bearer',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
final class OpenApiDefinition
{
}
