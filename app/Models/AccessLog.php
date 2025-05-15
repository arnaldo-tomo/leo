<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'authorized_person_id',
        'person_name',
        'status',
        'photo_path',
        'access_time',
    ];

    protected $casts = [
        'access_time' => 'datetime',
    ];

    public function authorizedPerson()
    {
        return $this->belongsTo(AuthorizedPerson::class);
    }
}