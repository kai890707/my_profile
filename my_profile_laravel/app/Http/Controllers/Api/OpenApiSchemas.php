<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

/**
 * OpenAPI Schema Definitions
 *
 * This file contains all schema definitions used across the API
 */
#[OA\Schema(
    schema: 'User',
    required: ['id', 'username', 'name', 'email', 'role', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'username', type: 'string', example: 'john_doe'),
        new OA\Property(property: 'name', type: 'string', example: '王小明'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'salesperson', 'user'], example: 'salesperson'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'inactive'], example: 'active'),
    ]
)]
#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['username', 'name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'username', type: 'string', minLength: 3, maxLength: 100, example: 'john_doe'),
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: '王小明'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', minLength: 6, example: 'password123'),
        new OA\Property(property: 'role', type: 'string', enum: ['user', 'salesperson'], example: 'salesperson'),
    ]
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
    ]
)]
#[OA\Schema(
    schema: 'AuthResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
        new OA\Property(
            property: 'data',
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                new OA\Property(property: 'access_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
                new OA\Property(property: 'refresh_token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGc...'),
                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'SalespersonProfile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 1),
        new OA\Property(property: 'company_id', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'full_name', type: 'string', example: '王小明'),
        new OA\Property(property: 'phone', type: 'string', pattern: '^09\d{8}$', example: '0912345678'),
        new OA\Property(property: 'bio', type: 'string', nullable: true, example: '資深業務員，專注於科技產業'),
        new OA\Property(property: 'specialties', type: 'string', nullable: true, example: 'SaaS, Cloud Solutions'),
        new OA\Property(property: 'service_regions', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3]),
        new OA\Property(property: 'approval_status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
        new OA\Property(property: 'avatar_url', type: 'string', nullable: true, example: '/api/profiles/1/avatar'),
    ]
)]
#[OA\Schema(
    schema: 'Company',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'ABC科技股份有限公司'),
        new OA\Property(property: 'tax_id', type: 'string', pattern: '^\d{8}$', example: '12345678'),
        new OA\Property(property: 'industry_id', type: 'integer', example: 1),
        new OA\Property(property: 'address', type: 'string', nullable: true, example: '台北市信義區信義路五段7號'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '02-12345678'),
        new OA\Property(property: 'website', type: 'string', nullable: true, example: 'https://abc-tech.com'),
        new OA\Property(property: 'approval_status', type: 'string', enum: ['pending', 'approved', 'rejected'], example: 'approved'),
    ]
)]
#[OA\Schema(
    schema: 'Industry',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: '科技業'),
        new OA\Property(property: 'slug', type: 'string', example: 'technology'),
    ]
)]
#[OA\Schema(
    schema: 'Region',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: '台北市'),
        new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]
        ),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Error message'),
    ]
)]
#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'from', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 10),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'to', type: 'integer', example: 15),
        new OA\Property(property: 'total', type: 'integer', example: 150),
    ]
)]
#[OA\Schema(
    schema: 'RegisterSalespersonRequest',
    required: ['name', 'email', 'password', 'password_confirmation', 'full_name', 'phone'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Jane Smith'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'jane@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
        new OA\Property(property: 'full_name', type: 'string', maxLength: 255, example: 'Jane Elizabeth Smith'),
        new OA\Property(property: 'phone', type: 'string', pattern: '^09\d{8}$', example: '0912345678'),
        new OA\Property(property: 'bio', type: 'string', maxLength: 1000, example: 'Experienced insurance salesperson'),
        new OA\Property(property: 'specialties', type: 'string', maxLength: 500, example: 'Life Insurance, Health Insurance'),
        new OA\Property(property: 'service_regions', type: 'array', items: new OA\Items(type: 'string'), example: ['台北市', '新北市']),
    ]
)]
#[OA\Schema(
    schema: 'StoreCompanyRequest',
    required: ['name'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 200, example: '三商美邦人壽股份有限公司'),
        new OA\Property(property: 'tax_id', type: 'string', maxLength: 50, example: '12345678', nullable: true),
        new OA\Property(property: 'is_personal', type: 'boolean', example: false),
    ]
)]
#[OA\Schema(
    schema: 'RejectSalespersonRequest',
    required: ['rejection_reason'],
    properties: [
        new OA\Property(property: 'rejection_reason', type: 'string', maxLength: 500, example: '資料不完整，請補充專業證照'),
        new OA\Property(property: 'reapply_days', type: 'integer', minimum: 0, maximum: 90, example: 7),
    ]
)]
final class OpenApiSchemas
{
    // This class only exists to hold OpenAPI schema definitions
    // No actual code needed
}
