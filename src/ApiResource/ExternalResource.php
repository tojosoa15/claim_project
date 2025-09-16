<?php
namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\ExternalApiController;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/api/foreign-exchance',
            controller: ExternalApiController::class,
            read: false,
            name: 'external_data'
        )
    ],
    paginationEnabled: false
)]
class ExternalResource
{
    public string $id;
    public string $name;
}