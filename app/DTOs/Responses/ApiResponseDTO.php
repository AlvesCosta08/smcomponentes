<?php
// app/DTOs/Responses/ApiResponseDTO.php

namespace App\DTOs\Responses;

class ApiResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly ?array $errors = null,
        public readonly int $statusCode = 200,
    ) {}

    public static function success(string $message, mixed $data = null, int $statusCode = 200): self
    {
        return new self(
            success: true,
            message: $message,
            data: $data,
            statusCode: $statusCode
        );
    }

    public static function error(string $message, ?array $errors = null, int $statusCode = 400): self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            statusCode: $statusCode
        );
    }

    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        if ($this->errors !== null) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}