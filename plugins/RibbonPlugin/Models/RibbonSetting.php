<?php
namespace Plugins\RibbonPlugin\Models;

use Illuminate\Database\Eloquent\Model;

class RibbonSetting extends Model
{
    protected $table = 'ribbon_plugin_settings';

    protected $fillable = [
        'left_text',
        'rfq_text',
        'rfq_url',
        'phone',
        'email',
    ];
}
