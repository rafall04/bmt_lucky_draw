<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Peserta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pesertas';

    protected $fillable = [
        'no_rekening',
        'nama',
        'alamat',
        'cabang',
        'status_menang',
        'hadiah_didapat',
        'waktu_menang',
    ];

    protected $casts = [
        'status_menang' => 'boolean',
        'waktu_menang' => 'datetime',
    ];
}

