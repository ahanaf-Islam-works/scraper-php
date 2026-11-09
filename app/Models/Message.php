<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends BaseModel
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = ['message'];
}
