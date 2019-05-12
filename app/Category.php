<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Icon;

/**
 * Class Category
 * @package App
 */
class Category extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function icons()
    {
        return $this->belongsToMany(Icon::class);
    }
}