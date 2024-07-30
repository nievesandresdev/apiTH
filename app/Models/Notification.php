<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends Model
{
    use HasFactory;

    protected $hidden = ['votes'];
    protected $appends = ['time'];

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'link_title',
        'link',
        'type'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, NotificationUser::class)
            ->withPivot('status', 'vote');
    }

    public function usersUnreadMessage(): BelongsToMany
    {
        return $this->belongsToMany(User::class, NotificationUser::class)
            ->wherePivot('status', 'unread')
            ->withPivot('status', 'vote');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, NotificationRole::class);
    }

    public function target(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function interaction(): HasOne
    {
        return $this->hasOne(NotificationUser::class);
    }

    public function getTimeAttribute()
    {
        return $this->created_at->format('H:i');
    }
}
