<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'description',
        'location',
        'start_date',
        'end_date',
        'max_participants',
        'requirements',
        'benefits',
        'images',
        'title',
        'activity_category',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
