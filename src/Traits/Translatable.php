<?php

namespace Statch\Translations\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;

trait Translatable
{
    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Boot the translatable trait for the model.
     */
    public static function bootTranslations()
    {
        static::deleted(function (self $model) {
            $model->translations()->delete();
        });
    }

    /**
     * Get all attached translations to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(config('statch-translations.model'), 'translatable');
    }

    public function determineLocale()
    {
        $model = app(config('statch-translations.model'));

        $locale = method_exists($model, 'defaultLocale') ? $model->defaultLocale() : \App::getLocale();

        return $locale;
    }

    /**
     * Translate the model to the given the current locale.
     *
     * @param string|null $locale
     *
     * @return \Statch\Translations\Models\Translation
     */
    public function getTranslations($locale = null)
    {
        if (!$this->relationLoaded('translations')) {
            $this->load('translations');
            // throw new \Exception("You must to eager-loader the relationship `translations`.");
        }

        return $this->translations->where('locale', $locale ?? $this->determineLocale())->first();
    }


    public function isTranslatableAttribute(string $key) : bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    public function getTranslatableAttributes() : array
    {
        return is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    /**
     * Overwrite getAttribute method to obtain by default the translation value.
     *
     * @param string|null $key
     *
     * @return
     */
    public function getAttribute($key)
    {
        if ($this->isTranslatableAttribute($key)) {
            $translation = $this->getTranslations();
            // $data = optional($translation)->data;
            $value = Arr::get($translation, "data." . $key);

            if (! empty($value)) {
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
    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($attributes as $key => $attribute) {
            $attributes[$key] = $this->getAttribute($key);
        }

        return $attributes;
    }
}
