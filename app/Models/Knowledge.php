<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Knowledge extends Model
{
    protected $fillable = [
        'collection',
        'content',
        'payload',
        'knowledge_id'
    ];
}
