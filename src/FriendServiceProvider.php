<?php

namespace TarekIM\Friends;

use Illuminate\Support\ServiceProvider;

class FriendServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$migrations = [];

		foreach(array_diff(scandir(__DIR__ . '/database/migrations/'), ['..', '.']) as $migration)
			$migrations[__DIR__ . '/database/migrations/' . $migration] = $this->getPath($migration, database_path('migrations'));

		$publishes = [
			'migrations' => $migrations
		];

		foreach ($publishes as $key => $value)
			$this->publishes($value, $key);
	}

	/**
	 * Get the full path to the migration.
	 *
	 * @param  string  $name
	 * @param  string  $path
	 * @return string
	 */
	protected function getPath($name, $path)
	{
		return $path . '/' . $this->getDatePrefix() . '_' . $name;
	}

	/**
	 * Get the date prefix for the migration.
	 *
	 * @return string
	 */
	protected function getDatePrefix()
	{
		return date('Y_m_d_His');
	}
}