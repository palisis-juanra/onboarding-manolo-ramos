<?php

namespace Services;

class ErrorHandlerService
{
	private $templateRenderer;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;
	}

	/**
	 * Renders the 404 error page.
	 *
	 * @return void
	 */
	public function renderNotFoundPage(string $errorMessage): void
	{
		$this->templateRenderer->renderTemplate(
			'_common/error/404',
			['errorMessage' => $errorMessage]
		);
		http_response_code(404);
		return;
	}
}