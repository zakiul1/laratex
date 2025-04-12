<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ribbon extends Model
{
    use HasFactory;

    protected $fillable = [
        'left_text',
        'phone',
        'email',
        'bg_color',
        'text_color',
    ];
}