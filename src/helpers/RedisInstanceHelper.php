<?php

namespace Helpers;

use Services\RedisService;

class RedisInstanceHelper extends RedisService
{
	private static $instance = null;
	protected $redis;

	private function __construct($host, $port, $password) 
	{
		parent::__construct($host, $port, $password);
		$this->redis = new RedisService($host, $port, $password);
	}

	/**
	 * Get an instance of the Redis Service (singleton pattern)
	 */
	public static function getInstance($host, $port, $password) 
	{
		if (self::$instance === null) {
			self::$instance = new self($host, $port, $password);
		}

		return self::$instance->redis;
	}
}