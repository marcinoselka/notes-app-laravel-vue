<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteLimitExceededException extends Exception
{
    public function __construct(private readonly int $limit)
    {
        parent::__construct("Osiągnięto limit {$limit} notatek na użytkownika.");
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'note_limit' => [$this->getMessage()],
            ],
        ], 422);
    }
}
