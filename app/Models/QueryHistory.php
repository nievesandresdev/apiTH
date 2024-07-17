<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueryHistory extends Model
{
    use HasFactory;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $table = 'query_history';

    protected $fillable = [
        'query_id',
        'qualification',
        'comment',
        'responded_at',
        'response_lang',
    ];

    protected $casts = [
        'comment' => 'array',
    ];


}
