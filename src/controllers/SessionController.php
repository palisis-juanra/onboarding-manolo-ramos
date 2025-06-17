<?php

namespace Controllers;

use Services\TemplateRenderService;

class SessionController
{
	// Initialize the TemplateRenderService
	private $templateRender;

	public function __construct()
	{
		$this->templateRender = new TemplateRenderService();
	}

	// TODO: redis, redir if logged, handle submit
	/**
	 * Handles the login request.
	 *
	 * This method processes the login request, validates the credentials,
	 * and returns a response indicating success or failure.
	 *
	 * @return void
	 */
	public function logIn()
	{
		$this->templateRender->renderTemplate('login/loginPage', []);
	}

	public function logOut()
	{
		$this->templateRender->renderTemplate('_common/logoutPage', []);
	}
}