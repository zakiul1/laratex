<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'type',
        'reference_id',
        'order',
    ];
    public function post()
    {
        return $this->belongsTo(\App\Models\Post::class, 'reference_id')->where('type', 'page');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }
}