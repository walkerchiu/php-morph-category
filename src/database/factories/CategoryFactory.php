<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\MorphCategory\Models\Entities\Category;
use WalkerChiu\MorphCategory\Models\Entities\CategoryLang;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'serial'     => $faker->isbn10,
        'identifier' => $faker->slug,
        'order'      => $faker->randomNumber
    ];
});

$factory->define(CategoryLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence
    ];
});
