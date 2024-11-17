<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationMessage extends Model
{
    protected $fillable =[
        'conversation_id',
        'user_id',
        'userPrompt',
        'systemPrompt',
        'action_type',
        'type_message',
        'content'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
