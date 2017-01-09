<?php

use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Concert::class, function (Faker\Generator $faker) {
	return [
		'title' => 'Unit Test Band',
        'subtitle' => 'with The Unit Support Solo',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 30000,
        'venue' => 'The Test Pit',
        'venue_address' => '123 Example Lane',
        'city' => 'Unitville',
        'state' => 'ON',
        'zip' => '90210',
        'additional_information' => 'For all unit tests, call (555) 555-5555.',
	];
});

$factory->state(App\Concert::class, 'published', function ($faker) {
    return [
        'published_at' => Carbon::parse('-1 week'),
    ];
});

$factory->state(App\Concert::class, 'unpublished', function ($faker) {
    return [
        'published_at' => null,
    ];
});