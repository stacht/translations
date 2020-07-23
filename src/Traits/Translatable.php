<?php

namespace Stacht\Translations\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\SoftDeletes;

trait Translatable
{
    protected $defaultLocale;

    /**
     * Boot the translatable trait for the model.
     */
    public static function bootTranslatable()
    {
        static::deleting(function ($model) {

            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (!$model->forceDeleting) {
                    return;
                }
            }

            $model->translations()->cursor()->each(fn ($translation) => $translation->delete());
        });
    }



    /**
     * Get all attached translations to the model.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(config('stacht-translations.model'), 'translatable');
    }

    public function determineLocale(): ?string
    {
        $model = app(config('stacht-translations.model'));
        $locale = App::getLocale();

        if (method_exists($model, 'defaultLocale')) {
            $locale = $model->defaultLocale();
        } else if (isset($this->defaultLocale)) {
            $locale = $this->defaultLocale;
        }

        return $locale;
    }

    public function usesTranslation($locale = 'en'): void
    {
        $this->defaultLocale = $locale;
    }

    /**
     * Translate the model to the given the current locale.
     *
     * @param string|null $locale
     *
     * @return \Stacht\Translations\Models\Translation
     */
    public function getTranslations($locale = null): ?\Stacht\Translations\Models\Translation
    {
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
            // throw new \Exception("You must to eager-loader the relationship `translations`.");
        }

        return $this->translations->where('locale', $locale ?? $this->determineLocale())->first();
    }


    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    public function getTranslatableAttributes(): array
    {
        return Arr::wrap($this->translatable);
    }

    /**
     * Overwrite getAttribute method to obtain by default the translation value.
     *
     * @param string|null $key
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            $translation = $this->getTranslations();
            // $data = optional($translation)->data;
            $value = Arr::get($translation, "data." . $key);

            if (!empty($value)) {
                return $value;
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Overwrite toArray method to works with Collections
     *
     * @return array
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();

        foreach ($attributes as $key => $attribute) {
            $attributes[$key] = $this->getAttribute($key);
        }

        return $attributes;
    }


    public function setTranslationsFromRequest($translationMatchingAttributes = [])
    {
        $fieldValues = request()->only($this->translatable);

        $this->setTranslations(request('locale'), $fieldValues, $translationMatchingAttributes);
    }

    public function setTranslations($locale, $fieldValues = [],  $translationMatchingAttributes = [])
    {
        $locale = $locale ?? 'en';

        // By default in English should update ONLY the original model
        if ($locale === 'en') {
            $this->fill($fieldValues);
        } else {
            // Otherwise the translations record should be created
            // @TODO: if the model isn;t stored in the database we should trigger some save?
            // if (!$this->exists) $this->save()
            $matchingAttributes = array_merge([
                'locale' => $locale,
            ], $translationMatchingAttributes);

            // Remove values that are equals to the original model fields.
            $fieldValues = array_filter($fieldValues, function ($value, $key) {
                return $this->{$key} !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (count($fieldValues) > 0) {
                $this->translations()->updateOrCreate(
                    $matchingAttributes,
                    [
                        'data' => $fieldValues
                    ]
                );
            }
        }
    }
}
