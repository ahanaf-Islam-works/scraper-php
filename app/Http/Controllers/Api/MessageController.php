<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    public function __construct(private MessageService $service) {}

    public function welcome(): JsonResponse
    {
        return response()->json([
            'message' => $this->service->welcomeMessage(),
        ]);
    }

    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = $this->service->createMessage(
            $request->validated('message')
        );

        return response()->json([
            'message' => 'Message saved',
            'data' => $message,
        ], 201);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->all(),
        ]);
    }

    public function show(Message $message): JsonResponse
    {
        return response()->json([
            'data' => $message,
        ]);
    }

    public function update(
        StoreMessageRequest $request,
        Message $message
    ): JsonResponse {
        $message = $this->service->update(
            $message,
            $request->validated('message')
        );

        return response()->json([
            'message' => 'Message updated',
            'data' => $message,
        ]);
    }

    public function destroy(Message $message): Response
    {
        $this->service->delete($message);

        return response()->noContent();
    }
}
