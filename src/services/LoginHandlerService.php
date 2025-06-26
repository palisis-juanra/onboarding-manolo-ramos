<?php

namespace Services;

class LoginHandlerService
{
	private $templateRenderer;

	public function __construct(TemplateRendererService $templateRenderer)
	{
		$this->templateRenderer = $templateRenderer;
	}

	/**
	 * Renders the login page.
	 *
	 * This method renders the login page template.
	 *
	 * @return void
	 */
	public function renderLoginPage(): void
	{
		$this->templateRenderer->renderTemplate('login/loginPage', []);
	}
}