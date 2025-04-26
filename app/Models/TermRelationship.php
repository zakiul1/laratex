<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TermRelationship extends Pivot
{
    protected $table = 'term_relationships';
    public $incrementing = false;
    protected $primaryKey = ['term_taxonomy_id', 'object_id', 'object_type'];
}