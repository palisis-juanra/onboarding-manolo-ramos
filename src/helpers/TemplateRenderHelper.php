<?php
namespace Helpers;

require_once __DIR__ . '/../../config/config.php';

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

class TemplateRenderHelper 
{
	private $mustache;

	/**
	 * TemplateRender constructor.
	 *
	 * Initializes the Mustache engine with the templates directory and sets up the loader.
	 */
	public function __construct() 
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
	 * Renders a template with the provided data.
	 *
	 * @param string $templateName The name of the template to render.
	 * @param array $data The data to pass to the template.
	 */
	public function renderTemplate($templateName, $data) 
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
	public function renderPartial($partialName, $data) 
	{
		echo $this->mustache->renderPartial($partialName, $data);
	}

	/**
	 * Builds data for rendering.
	 *
	 * This method can be used to prepare or transform data before rendering it with Mustache.
	 *
	 * @param array $data The data to be processed.
	 */
	public function buildTemplateData($data) 
	{
		// TODO: implement logic
		return $data;
	}
}