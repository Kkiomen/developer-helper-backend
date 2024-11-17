<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentationFile extends Model
{
    protected $fillable = [
        'filename',
        'md5'
    ];
}
