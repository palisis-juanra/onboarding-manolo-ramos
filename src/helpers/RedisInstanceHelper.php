<?php

namespace Helpers;

use Services\RedisService;

class RedisInstanceHelper extends RedisService
{
	protected $redis;
	private static $instance = null;

	private function __construct($redisConfig) 
	{
		parent::__construct(
			$redisConfig['REDIS_HOST'],
			$redisConfig['REDIS_PORT'],
			$redisConfig['REDIS_PASSWORD']
		);
		$this->redis = new RedisService(
			$redisConfig['REDIS_HOST'],
			$redisConfig['REDIS_PORT'],
			$redisConfig['REDIS_PASSWORD']
		);
	}

	/**
	 * Get an instance of the Redis Service (singleton pattern)
	 */
	public static function getInstance($redisConfig) 
	{
		if (self::$instance === null) {
			self::$instance = new self(
				$redisConfig
			);
		}

		return self::$instance->redis;
	}
}