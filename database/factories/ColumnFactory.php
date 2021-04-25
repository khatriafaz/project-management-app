<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Column;
use Faker\Generator as Faker;

$factory->define(Column::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
    ];
});
