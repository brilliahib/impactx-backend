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

    protected $casts = [
        'activity_category' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'activity_participants')
            ->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
