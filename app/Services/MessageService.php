<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class MessageService
{
    public function welcomeMessage(): string
    {
        return 'Hello welcome';
    }

    public function createMessage(string $message): Message
    {
        return Message::query()->create([
            'message' => $message,
        ]);
    }

    public function withEmoji(string $message): string
    {
        return $message.' 🎉';
    }

    public function getMessage(int $id): Message
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'ID must be a positive integer.'
            );
        }

        return Message::query()->findOrFail($id);
    }

    public function all(): Collection
    {
        return Message::query()
            ->get();
    }

    public function update(Message $message, string $messageText): Message
    {
        $message->update([
            'message' => $messageText,
        ]);

        return $message->refresh();
    }

    public function delete(Message $message): void
    {
        $message->delete();
    }
}
