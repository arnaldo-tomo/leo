<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorizedPerson extends Model
{
    use HasFactory;

    // Apontar para a tabela existente
    protected $table = 'authorized_persons';

    protected $fillable = [
        'name',
        'photo_path',
        'face_descriptor',
        'access_level',
        'notes',
        'active'
    ];

    protected $casts = [
        'face_descriptor' => 'array',
        'active' => 'boolean',
    ];

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }
}