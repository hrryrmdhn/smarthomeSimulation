<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory;

    // yang bisa diisi kolom tabel database nya
    protected $fillable = [
        'label',
        'status'
    ];

    // kolom status harus boolean
    protected $casts = [
        'status' => 'boolean'
    ];
}
