<?php

return [
    /*
     * When using the "Translatable" trait from this package, we need to know which
     * Eloquent model should be used to retrieve your translations. Of course, it
     * is often just the "Translation" model but you may use whatever you like.
     *
     * The model you want to use as a Translation model needs to implement the
     * `Statch\Translations\Contracts\Translation` contract.
     */
    'model' => \Statch\Translations\Models\Translation::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant table
    |--------------------------------------------------------------------------
    |
    | Default tenant table
    |
    */
    'table' => 'translations',
];
