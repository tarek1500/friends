<?php

namespace TarekIM\Friends;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use TarekIM\Friends\Events\AcceptFriend;
use TarekIM\Friends\Events\BlockFriend;
use TarekIM\Friends\Events\CancelRequest;
use TarekIM\Friends\Events\DenyFriend;
use TarekIM\Friends\Events\FriendRequest;
use TarekIM\Friends\Events\UnblockFriend;
use TarekIM\Friends\Events\Unfriend;

trait Friendable
{
	/**
	 * Get the Eloquent query builder for all friendships for the current model instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function friendships()
	{
		return Friend::where(function ($query) {
			$query->whereSender($this)
				  ->orWhereReceiver($this);
		});
	}

	/**
	 * Get the Eloquent query builder for the given model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $sender  The receiver, or the sender if the second argument is a Model.
	 * @param  \Illuminate\Database\Eloquent\Model|bool  $receiver  The self flag, or the receiver if this argument is a Model.
	 * @param bool $self  The self flag to indicate if it is one way relation only from the sender.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function friendship(Model $sender, $receiver = false, bool $self = false)
	{
		[$sender, $receiver, $self] = $this->prepareArguments($sender, $receiver, $self);

		return Friend::where(function ($query) use ($sender, $receiver, $self) {
			$query->where(function ($query) use ($sender, $receiver) {
				$query->whereSender($sender)
					  ->whereReceiver($receiver);
			})->when(!$self, function ($query) use ($sender, $receiver) {
				$query->orWhere(function ($query) use ($sender, $receiver) {
					$query->whereSender($receiver)
						  ->whereReceiver($sender);
				});
			});
		});
	}

	/**
	 * Prepare the sender and receiver.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $sender
	 * @param  \Illuminate\Database\Eloquent\Model|bool  $receiver
	 * @param  bool  $self
	 *
	 * @return array
	 */
	private function prepareArguments($sender, $receiver, $self)
	{
		return $receiver instanceof Model ? [$sender, $receiver, $self]
										  : [$this, $sender, $receiver];
	}

	/**
	 * Send friend request to another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return \TarekIM\Friends\Friend|null
	 */
	public function sendFriendRequest(Model $model)
	{
		if ($this->is($model))
			return null;

		$friendship = $this->friendship($model)->first();

		if (!is_null($friendship))
			return null;

		$data = [
			'sender_type' => $this->getMorphClass(),
			'sender_id' => $this->getKey(),
			'receiver_type' => $model->getMorphClass(),
			'receiver_id' => $model->getKey(),
			'status' => Status::Pending
		];

		$friendship = Friend::create($data);

		event(new FriendRequest($this, $model));

		return $friendship;
	}

	/**
	 * Block another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return \TarekIM\Friends\Friend|null
	 */
	public function blockFriend(Model $model)
	{
		if ($this->is($model))
			return null;

		$friendship = $this->friendship($model)->first();

		if (!is_null($friendship))
		{
			if ($friendship->getOriginal('status') == Status::Block)
				return null;

			$friendship->delete();
		}

		$data = [
			'sender_type' => $this->getMorphClass(),
			'sender_id' => $this->getKey(),
			'receiver_type' => $model->getMorphClass(),
			'receiver_id' => $model->getKey(),
			'status' => Status::Block
		];

		$friendship = Friend::create($data);

		event(new BlockFriend($this, $model));

		return $friendship;
	}

	/**
	 * Unblock another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return bool
	 */
	public function unblockFriend(Model $model)
	{
		if ($this->is($model))
			return false;

		$friendship = $this->friendship($model, true)->first();

		if (is_null($friendship) || $friendship->getOriginal('status') != Status::Block)
			return false;

		$value = $friendship->delete() ?? false;

		event(new UnblockFriend($this, $model));

		return $value;
	}

	/**
	 * Cancel a model instance friend request.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return bool
	 */
	public function cancelFriendRequest(Model $model)
	{
		if ($this->is($model))
			return false;

		$friendship = $this->friendship($model, true)->first();

		if (is_null($friendship) || $friendship->getOriginal('status') != Status::Pending)
			return false;

		$value = $friendship->delete() ?? false;

		event(new CancelRequest($this, $model));

		return $value;
	}

	/**
	 * Accept a friend request from another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return \TarekIM\Friends\Friend|null
	 */
	public function acceptFriendRequest(Model $model)
	{
		if ($this->is($model))
			return null;

		$friendship = $this->friendship($model, $this, true)->first();

		if (is_null($friendship) || $friendship->getOriginal('status') != Status::Pending)
			return null;

		$friendship->update(['status' => Status::Accept]);

		event(new AcceptFriend($this, $model));

		return $friendship;
	}

	/**
	 * Deny a friend request from another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return bool
	 */
	public function denyFriendRequest(Model $model)
	{
		if ($this->is($model))
			return false;

		$friendship = $this->friendship($model, $this, true)->first();

		if (is_null($friendship) || $friendship->getOriginal('status') != Status::Pending)
			return false;

		$value = $friendship->delete() ?? false;

		event(new DenyFriend($this, $model));

		return $value;
	}

	/**
	 * Remove a friendship belongs to another model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return bool
	 */
	public function unfriend(Model $model)
	{
		if ($this->is($model))
			return false;

		$friendship = $this->friendship($model)->first();

		if (is_null($friendship) || $friendship->getOriginal('status') != Status::Accept)
			return false;

		$value = $friendship->delete() ?? false;

		event(new Unfriend($this, $model));

		return $value;
	}

	/**
	 * Get the Eloquent query builder for all pending requests sent to the current model instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getPending()
	{
		return Friend::where(function ($query) {
			$query->whereReceiver($this);
		})->whereStatus(Status::Pending);
	}

	/**
	 * Get the Eloquent query builder for all friends for the current model instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getFriends()
	{
		return $this->friendships()->whereStatus(Status::Accept);
	}

	/**
	 * Get the Eloquent query builder for all blocked model instances by the current model instance.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getBlocked()
	{
		return Friend::where(function ($query) {
			$query->whereSender($this);
		})->whereStatus(Status::Block);
	}

	/**
	 * Dynamically retrieve attributes on the model.
	 * NOTE: Make sure the model doesn't has any attribute name like 'friendships', 'getPending', 'pending', 'getFriends', 'friends', 'getBlocked' or 'blocked'.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (Friend::$enableDynamicAttributes)
		{
			if (method_exists($this, $key))
			{
				$query = $this->$key();

				if ($query instanceof EloquentBuilder)
					return $query->get();
			}
			else if (in_array($key, ['pending', 'friends', 'blocked']))
				return $this->{'get' . Str::studly($key)}()->get();
		}

		return parent::__get($key);
	}
}