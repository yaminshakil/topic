<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    public $timestamps = false;

    protected $fillable = ['slug', 'name', 'icon', 'badge', 'sort_order'];

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class);
    }
}
