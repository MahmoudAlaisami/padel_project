<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemCategory extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function itemTypes(): HasMany
    {
        return $this->hasMany(ItemType::class, 'category_id');
    }
}
