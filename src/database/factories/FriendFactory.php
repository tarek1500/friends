<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\User;
use Faker\Generator as Faker;
use TarekIM\Friends\Friend;
use TarekIM\Friends\Status;

$factory->define(Friend::class, function (Faker $faker) {
	[$sender, $receiver] = User::inRandomOrder()->take(2)->get();

	return [
		'sender_type' => $sender->getMorphClass(),
		'sender_id' => $sender->getKey(),
		'receiver_type' => $receiver->getMorphClass(),
		'receiver_id' => $receiver->getKey(),
		'status' => Status::getRandomStatus()
	];
});