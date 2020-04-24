<?php

namespace Stacht\Translations\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface Translation
{
    public function translatable(): MorphTo;

    public function scopeLocale(Builder $builder, string $locale): Builder;
}
