<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_inspires',
        'work_prefer',
        'work_challenge',
        'work_hardskills',
        'work_softskills',
        'work_roles',
    ];

    protected $casts = [
        'work_hardskills' => 'array',
        'work_softskills' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
