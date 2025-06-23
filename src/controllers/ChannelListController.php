<?php

namespace Controllers;

use Services\ChannelListHandlerService;

class ChannelListController 
{
	// Service instances
	protected $channelListHandler;

	public function __construct()
	{
		$this->channelListHandler = new ChannelListHandlerService;
	}

	public function showChannelList()
	{
		$this->channelListHandler->showChannelList();
	}

	public function renderChannelListPage()
	{
		$this->channelListHandler->renderChannelListPage();
	}

	public function submitChannelPick()
	{
		$this->channelListHandler->submitChannelPick();
	}
}