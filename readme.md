# Laravel Translations

Simple Laravel Translation package.
The translation mechanism is very simple. The original `data` (in the default language) should be stored in your translatable model. The contents in any other language should be stored in the translations model.

Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require stacht/translations
```

Publish configuration

```bash
$ php artisan vendor:publish --provider="Stacht\Translations\TranslationsServiceProvider" --tag="config"
```

Publish migration

```bash
$ php artisan vendor:publish --provider="Stacht\Translations\TranslationsServiceProvider" --tag="migrations"
```



## Usage

The key of making models translatable is to add the `Translatable` trait to them. By adding the trait, the package initializes a polimorphic relationship between the translatable model and the translation model automatically.

```php
use Stacht\Translations\Traits\Translatable;

class Post extends Model
{
    use Translatable;


    /**
     * The attributes that could be translated.
     *
     * @var array
     */
    protected $translatable = [
        'name',
    ];
}
```

You can retrieve all the translations of a model by using the `translations`property. It returns a collection of the translations. You can handle them as any other Eloquent collection.

```php
// Returns the collection of translations
$post->translations;
```

Also, if you want to get a translation of a given language, you can use the `translate()` method on the model. If you omit the langauge, the method will use the current app language to get the translation. It returns the translation paired the given language or returns `null` if there is no translation.

```php
// Returns the spanish translation model instance if present
$post->translate(['locale' => 'es']);
```

If you need only the translation paired with the current application language, you can use the `translation` property. Since, it uses the `translate()`method, if there is no translation for the current language, it returns `null`.

```php
App::setLocale('es');

// Returns the spanish translation if present
$post->translation;

$post->usesTranslation('it');

// Returns the Italian translation if present
$post->translation;
```

> If you want to append the translation property to the array / JSON representation of the model, don't forget to add it to the `$translatable` array.



#### The Translations Migrations

The includes the migration for the translations. The translation `data` is in JSON data type, what makes possible to use special MySql JSON syntax on the `data` column.

```php
$post->translation->update(['data->title' => 'New Title']);
```

> You need MySql 5.7+ to use the JSON feature.

If your database engine does not support JSON, it will be stored as a text format and we cast it as an array on the Laravel's end.

Why JSON? With JSON we can represent the translatable model's structure without any restrictions. Flexible, yet simple solution.



#### The Translations Model

The models full namespace is `Stacht\Translation\Models\Translation`. You can use it as any other model, no suprises here.



#### Creating Translations

There is no fixed way to create translations. You can do it in several ways, it's totally up to you.

We show a very basic example to point the structure you need to follow when creating a new translation. Let's say we have nested controllers with the following route structure:

```php
# Posts
Route::resource('posts', 'PostsController');

# Translations
Route::resource('posts.translations', 'TranslationsController');
```

The store method at the TranslationsController should look like the following:

```php
// app/Http/Controllers/TranslationsController

public function store(Request $request, Post $post)
{
    $post->translations()->create([
        'locale' => 'es',
        'data' => [
            'title' => $request->translation_title,
            'body' => $request->translation_body,
        ],
    ]);
}
```

The language attribute is required and every locale can be stored once for every post. Since, we automatically cast the `data` as an array, we have to provide an array when creating a model, and the rest will be done by Laravel.

Here you need to pay attention, you need to "recreate" the structure of the model what is being translated.



## Extending

If you need to EXTEND the existing `Translation` model note that:

- Your `Translation` model needs to extend the `Stacht\Translations\Models\Translation` model

If you need to REPLACE the existing `Translation` model  you need to keep the following things in mind:

- Your `Translation` model needs to implement the `Stacht\Translations\Contracts\Translation` contract

In BOTH cases, whether extending or replacing, you will need to specify your new model in the configuration. To do this you must update the `model` value in the configuration file after publishing the configuration with this command:

```
php artisan vendor:publish --provider="Stacht\Translations\TranslationsServiceProvider" --tag="config"
```



## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email corrado.striuli@gmail.com instead of using the issue tracker.

## Credits

- [Corrado Striuli][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[link-author]: https://bitbucket.com/stacht
[link-contributors]: ../../contributors
