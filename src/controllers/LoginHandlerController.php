<?php

namespace Controllers;

use Services\LoginHandlerService;
use Services\TemplateRendererService;

class LoginHandlerController
{
	private $templateRenderer;
	private $loginHandlerService;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;

		$this->loginHandlerService = new LoginHandlerService(
			$this->templateRenderer
		);
	}

	public function index(): void
	{
		$this->loginHandlerService->renderLoginPage();
	}
}