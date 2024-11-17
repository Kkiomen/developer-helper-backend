<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable =[
        'user_id',
        'session_hash',
        'active'
    ];

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class)->orderBy('created_at', 'asc');
    }
}
