<?php

namespace TarekIM\Friends\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class FriendEvent
{
	use SerializesModels;

	/**
	 * The sender model instance.
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	public $sender;

	/**
	 * The sender model instance.
	 *
	 * @var \Illuminate\Database\Eloquent\Model
	 */
	public $receiver;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct(Model $sender, Model $receiver)
	{
		$this->sender = $sender;
		$this->receiver = $receiver;
	}
}