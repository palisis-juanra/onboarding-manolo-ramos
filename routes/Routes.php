<?php

namespace Routes;

class Routes 
{
	protected static $routes = [
		'/' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'login',
			],
		],
		'/login' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'login',
			]
		],
		'/login/loginAction' => [
			'POST' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'POST',
				'action' => 'login',
			],
		],
		'/logout' => [
			'GET' => [
				'controller' => 'handleSessionHandlerService',
				'method' => 'GET',
				'action' => 'logout',
			],
		],
		'/dashboard' => [
			'GET' => [
				'controller' => 'handleChannelListController',
				'method' => 'GET',
				'action' => 'showChannelList',
			]
		],
		'/dashboard/pickChannel' => [
			'POST' => [
				'controller' => 'handleChannelListController',
				'method' => 'POST',
				'action' => 'pickChannel',
			]
		],
	];

	public static function getRoutes() {
		return self::$routes;
	}
}
