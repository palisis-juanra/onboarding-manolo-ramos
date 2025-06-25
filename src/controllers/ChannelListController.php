<?php

namespace Controllers;

use Services\ChannelListHandlerService;
use Services\RedisService;
use Services\TemplateRendererService;
use TourCMS\Utils\TourCMS;

class ChannelListController 
{
	// Service instances
	protected $channelListHandler;

	public function __construct(
		TourCMS 				$tourCMSclient, 
		RedisService 			$redisClient, 
		TemplateRendererService $templateRenderer
	)
	{
		$this->channelListHandler = new ChannelListHandlerService(
			$tourCMSclient,
			$redisClient,
			$templateRenderer
		);
	}

	public function index(): void
	{
		$this->channelListHandler->renderChannelListPage();
	}

	public function store(): void
	{
		$this->channelListHandler->submitChannelPick();
	}
}