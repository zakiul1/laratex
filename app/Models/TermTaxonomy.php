<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Media;
use App\Models\TermTaxonomyImage;
use App\Models\Product;

class TermTaxonomy extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'term_taxonomies';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'term_taxonomy_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Always eager-load images relationship.
     *
     * @var array<int, string>
     */
    protected $with = ['images'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'term_id',
        'taxonomy',
        'description',
        'parent',
        'count',
        'status',
    ];

    /**
     * The term this taxonomy belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id', 'id');
    }

    /**
     * Parent taxonomy in the same table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentTaxonomy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent', 'term_taxonomy_id');
    }

    /**
     * Child taxonomies (if any) in the same table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent', 'term_taxonomy_id');
    }

    /**
     * All the media items attached to this taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(
            Media::class,
            'media_term_taxonomy',
            'term_taxonomy_id',
            'media_id'
        )
            ->wherePivot('object_type', 'media')
            ->withPivot('object_type')
            ->withTimestamps();
    }

    /**
     * Additional images linked to this taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(
            TermTaxonomyImage::class,
            'term_taxonomy_id',
            'term_taxonomy_id'
        );
    }

    /**
     * All products attached to this taxonomy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'term_relationships',
            'term_taxonomy_id',
            'object_id'
        )
            ->wherePivot('object_type', 'product')
            ->withPivot('object_type')
            ->withTimestamps();
    }
}