<?php

namespace Helpers;

use Services\RedisService;
use Services\RedisServiceException;

class RedisInstanceHelper extends RedisService
{
	protected $redis;
	private static ?RedisInstanceHelper $instance = null;

	/**
	 * @throws RedisServiceException
	 */
	private function __construct(array $redisConfig)
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
	 * @throws RedisServiceException
	 */
	public static function getInstance(array $redisConfig): RedisService
	{
		if (self::$instance === null) {
			self::$instance = new self($redisConfig);
		}

		return self::$instance->redis;
	}
}