<?php

namespace TarekIM\Friends;

use Illuminate\Support\Arr;

class Status
{
	const Pending = 0;
	const Accept = 1;
	const Block = 2;

	/**
	 * Convert given status to its equivalent name.
	 *
	 * @param  int  $status
	 *
	 * @return string|null
	 */
	public static function getName(int $status)
	{
		switch ($status)
		{
			case Status::Pending:
				return 'Pending';
			case Status::Accept:
				return 'Accept';
			case Status::Block:
				return 'Block';
			default:
				return null;
		}
	}
}