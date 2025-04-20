<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'provider',  // ← add this
        'enabled',
    ];
}
