<?php

namespace TarekIM\Friends;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
	/**
	 * Enable dynamic attributes retrieving.
	 *
	 * @var bool
	 */
	public static $enableDynamicAttributes = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'sender_type', 'sender_id', 'receiver_type', 'receiver_id', 'status'
	];

	/**
	 * Get the sender that the friendship belongs to.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function sender()
	{
		return $this->morphTo();
	}

	/**
	 * Get the receiver that the friendship belongs to.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function receiver()
	{
		return $this->morphTo();
	}

	/**
	 * Scope a query to include where statement on the sender.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $boolean
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereSender(EloquentBuilder $query, Model $model, $boolean = 'and')
	{
		$this->whereQuery($query, $model, 'sender', $boolean);
	}

	/**
	 * Scope a query to include orWhere statement on the sender.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $boolean
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOrWhereSender(EloquentBuilder $query, Model $model)
	{
		$this->scopeWhereSender($query, $model, 'or');
	}

	/**
	 * Scope a query to include where statement on the receiver.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $boolean
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeWhereReceiver(EloquentBuilder $query, Model $model, $boolean = 'and')
	{
		$this->whereQuery($query, $model, 'receiver', $boolean);
	}

	/**
	 * Scope a query to include orWhere statement on the receiver.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $boolean
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOrWhereReceiver(EloquentBuilder $query, Model $model)
	{
		$this->scopeWhereReceiver($query, $model, 'or');
	}

	/**
	 * Query helper function to include where statement to given query.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  \Illuminate\Database\Eloquent\Model  $model
	 * @param  string  $name
	 * @param  string  $boolean
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function whereQuery(EloquentBuilder $query, Model $model, string $name, string $boolean)
	{
		[$type, $id] = $model->getMorphs($name, null, null);

		return $query->where(function ($query) use ($model, $type, $id) {
			$query->where($type, $model->getMorphClass())
				  ->where($id, $model->getKey());
		}, null, null, $boolean);
	}
}