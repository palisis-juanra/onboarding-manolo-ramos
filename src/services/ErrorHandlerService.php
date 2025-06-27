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
		// Set the error message if it is not empty
		$templateData = [];

		if (!empty($errorMessage)) {
			$templateData['errorMessage'] = $errorMessage;
		}

		$this->templateRenderer->renderTemplate(
			'_common/error/404',
			$templateData
		);

		http_response_code(404);
		return;
	}
}