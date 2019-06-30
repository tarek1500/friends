<?php

use App\User;
use Illuminate\Database\Seeder;
use TarekIM\Friends\Friend;
use TarekIM\Friends\Status;

class FriendsTableSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$count = $this->command->ask('How many friendship(s) do you need?', 50);
		$this->command->info("Creating {$count} friendship(s).");

		for ($i = 0; $i < $count; $i++)
		{
			[$sender, $receiver] = User::inRandomOrder()->take(2)->get();
			$friendship = $sender->friendship($receiver)->first();

			if (is_null($friendship))
			{
				Friend::create([
					'sender_type' => $sender->getMorphClass(),
					'sender_id' => $sender->getKey(),
					'receiver_type' => $receiver->getMorphClass(),
					'receiver_id' => $receiver->getKey(),
					'status' => Status::getRandomStatus()
				]);
			}
		}

		$this->command->info('Friendship(s) Created!');
	}
}