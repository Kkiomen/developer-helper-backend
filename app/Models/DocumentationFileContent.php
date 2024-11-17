<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentationFileContent extends Model
{
    protected $fillable = [
        'filename',
        'md5',
        'embedded',
        'parse_content',
        'header'
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'embedded' => 'array',
        ];
    }
}
