<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'login' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'logout' => [
			'details' => [
				'controller' => 'handleSessionController',
				'method' => 'GET',
				'action' => 'logout',
			],
		]
	];

	public static function getRoutes() {
		return self::$routes;
	}
}
