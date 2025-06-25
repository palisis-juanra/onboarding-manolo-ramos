<?php

namespace Services;

use Mustache_Engine;
use Mustache_Exception_UnknownTemplateException;
use Mustache_Loader_FilesystemLoader;

class TemplateRendererService 
{
	private static $instance;
	private $mustache;

	/**
	 * TemplateRender constructor.
	 *
	 * Initializes the Mustache engine with the templates directory and sets up the loader.
	 */
	private function __construct() 
	{
		// Init
		$templatesPath = realpath(dirname(__FILE__) . '/../../templates');
		$extensions = ['extension' => '.html'];
		$loader = new Mustache_Loader_FilesystemLoader($templatesPath, $extensions);
		$mustacheEngineArgs = [
			'entity_flags' => ENT_QUOTES,
			'loader' => $loader,
			'partials_loader' => $loader,
		];

		// Instance the Mustache engine
		$this->mustache = new Mustache_Engine($mustacheEngineArgs);
	}

	/**
	 * Returns the singleton instance of the TemplateRenderer Service.
	 *
	 * This method ensures that only one instance of the TemplateRenderer Service is created
	 * and returns that instance. If the instance does not exist, it creates a new one.
	 *
	 * @return TemplateRendererService The singleton instance of the TemplateRenderer Service.
	 */
	public static function getInstance(): TemplateRendererService
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/**
	 * Renders a template with the provided data.
	 *
	 * @param string $templateName The name of the template to render.
	 * @param array $data The data to pass to the template.
	 */
	public function renderTemplate(string $templateName, array $data): void
	{
		try {
			echo $this->mustache->render($templateName, $data);
		} catch (Mustache_Exception_UnknownTemplateException $e) {
			echo $this->mustache->render('common/404', $data);
		}
	}

	/**
	 * Renders a partial template with the provided data.
	 *
	 * @param string $partialName The name of the partial template to render.
	 * @param array $data The data to pass to the partial template.
	 */
	public function renderPartial(string $partialName, array $data): void
	{
		echo $this->mustache->renderPartial($partialName, $data);
	}
}