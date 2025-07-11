<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationPms extends Model
{
    use HasFactory;

    public $table = 'integration_pms';

    public $fillable = [
        'hotel_id',
        'name_pms',
        'url_pms',
        'with_url',
        'email_pms',
        'password_pms',
    ];

    public $casts = [
        'with_url' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
