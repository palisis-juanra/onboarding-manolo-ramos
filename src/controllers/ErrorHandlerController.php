<?php

namespace Controllers;

use Services\ErrorHandlerService;
use Services\TemplateRendererService;

class ErrorHandlerController
{
	private $templateRenderer;
	private $errorHandlerService;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;

		$this->errorHandlerService = new ErrorHandlerService(
			$this->templateRenderer
		);
	}

	public function index(string $errorMessage): void
	{
		$this->errorHandlerService->renderNotFoundPage($errorMessage);
	}
}