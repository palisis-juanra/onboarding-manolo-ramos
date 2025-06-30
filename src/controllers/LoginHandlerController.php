<?php

namespace Controllers;

use Services\LoginHandlerService;
use Services\TemplateRendererService;

class LoginHandlerController
{
	private $templateRenderer;
	private $loginHandler;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;

		$this->loginHandler = new LoginHandlerService(
			$this->templateRenderer
		);
	}

	public function index(): void
	{
		$this->loginHandler->renderLoginPage();
	}
}