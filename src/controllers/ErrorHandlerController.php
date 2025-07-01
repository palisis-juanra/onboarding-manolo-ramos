<?php

namespace Controllers;

use Services\ErrorHandlerService;
use Services\TemplateRendererService;

class ErrorHandlerController
{
	private $templateRenderer;
	private $errorHandler;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;

		$this->errorHandler = new ErrorHandlerService(
			$this->templateRenderer
		);
	}

	public function index(string $errorMessage): void
	{
		$this->errorHandler->renderNotFoundPage($errorMessage);
	}

	public function getErrorMessage(string $errorCode)
	{
		return $this->errorHandler->getErrorMessage($errorCode);
	}
}