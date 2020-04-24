<?php

namespace Statch\Translations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Statch\Translations\Contracts\Translation as TranslationContract;

class Translation extends Model implements TranslationContract
{
    protected $fillable = [
        'locale',
        'data',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    public static function rules(): array
    {
        return [
            'translatable_id' => 'required|integer',
            'translatable_type' => 'required|string',
            'locale' => 'required|string|max:10',
            'data' => 'required|text',
       ];
    }

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statch-translations.table'));
    }

    /**
     * Get the owner model of the translation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo('translatable', 'translatable_type', 'translatable_id');
    }

    /**
     * Scope translations by the given locale.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string                                $locale
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLocale(Builder $builder, string $locale): Builder
    {
        return $builder->where('locale', $locale);
    }
}
