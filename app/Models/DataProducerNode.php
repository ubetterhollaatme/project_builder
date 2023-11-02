<?php

namespace App\Models;

use App\Casts\Phone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class DataProducerNode extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'name',
        'desc',
        'phone',
        'email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'phone' => Phone::class,
    ];
}
