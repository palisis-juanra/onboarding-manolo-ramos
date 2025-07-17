<?php

namespace Services;

use Helpers\ErrorHandlerHelper;

class ErrorHandlerService
{
	private TemplateRendererService $templateRenderer;
	private array $errorMessagesList;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;
		$this->errorMessagesList = ErrorHandlerHelper::getErrorMessages();
	}

	/**
	 * Renders a generic error page portraying the error message.
	 *
	 * @return void
	 */
	public function renderNotFoundPage(string $errorMessage): void
	{
		$templateData = [];

		if (!empty($errorMessage)) {
			$templateData['errorMessage'] = $errorMessage;
		}

		$this->templateRenderer->renderTemplate(
			'_common/error/errorPage',
			$templateData
		);

		http_response_code(500);
		exit;
	}

	/**
	 * Returns an error message which describes the current problem or crash
	 * that the application is experiencing.
	 *
	 * @return string 
	 */
	public function getErrorMessage(string $errorCode): string
	{
		foreach ($this->errorMessagesList as $errorType => $messages) {
			if (array_key_exists($errorCode, $messages)) {
				return $messages[$errorCode];
			}
		}

		return 'Unknown error code: ' . $errorCode;
	}
}