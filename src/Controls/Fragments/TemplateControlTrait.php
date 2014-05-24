<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls\Fragments;

use Nette;
use Nette\Application\UI\IRenderable;
use Nette\Application\UI\Presenter;


/**
 * TemplateControlTrait
 * @author Jan Skrasek
 */
trait TemplateControlTrait
{
	use ComponentControlTrait;


	/** @var string */
	protected $templateFile;


	public function setTemplateFile($file)
	{
		if (!file_exists($file)) {
			throw new Nette\InvalidArgumentException("File does not exists '$file'.");
		}

		$this->templateFile = $file;
		return $this;
	}


	public function getTemplateFile()
	{
		return $this->templateFile;
	}


	/**
	 * Returns form control
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$this->control->setHtml($this->toString());
		return $this->control;
	}


	/**
	 * Common render method.
	 * @return void
	 */
	protected function beforeRender()
	{
	}


	/**
	 * Renders form control
	 * @return void
	 */
	public function render()
	{
		echo $this->toString();
	}


	/**
	 * Returns rendered template
	 * @return string
	 */
	public function toString()
	{
		$this->beforeRender();
		return (string) $this->getTemplate();
	}


	/**** Application\UI\Control **************************************************************************************/


	/** @var Nette\Templating\ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = array();

	/** @var bool */
	public $snippetMode;


	/********************* template factory ****************d*g**/


	/**
	 * @return Nette\Templating\ITemplate
	 */
	final public function getTemplate()
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!$value instanceof Nette\Templating\ITemplate && $value !== NULL) {
				$class2 = get_class($value); $class = get_class($this);
				throw new Nette\UnexpectedValueException("Object returned by $class::createTemplate() must be instance of Nette\\Templating\\ITemplate, '$class2' given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}


	/**
	 * @param  string|NULL
	 * @return Nette\Templating\ITemplate
	 * @modified Jan Skrasek
	 */
	protected function createTemplate($class = NULL)
	{
		$templateFile = $this->templateFile ?: dirname($this->reflection->getFileName()) . '/' . $this->reflection->getShortName() . '.latte';

		$template = $class ? new $class : new Nette\Templating\FileTemplate($templateFile);
		$presenter = $this->getPresenter(FALSE);
		$template->onPrepareFilters[] = $this->templatePrepareFilters;
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		// default parameters
		$template->control = $template->_control = $this;
		$template->presenter = $template->_presenter = $presenter;
		$template->form = $template->_form = $this;
		if ($presenter instanceof Presenter) {
			$template->setCacheStorage($presenter->getContext()->getService('nette.templateCacheStorage'));
			$template->user = $presenter->getUser();
			$template->netteHttpResponse = $presenter->getContext()->getByType('Nette\Http\IResponse');
			$template->netteCacheStorage = $presenter->getContext()->getByType('Nette\Caching\IStorage');
			$template->baseUri = $template->baseUrl = rtrim($presenter->getContext()->getByType('Nette\Http\IRequest')->getUrl()->getBaseUrl(), '/');
			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  Nette\Templating\Template
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		$template->registerFilter($this->getPresenter()->getContext()->createService('nette.latte'));
	}


	/********************* rendering ****************d*g**/


	/**
	 * Forces control or its snippet to repaint.
	 * @param  string
	 * @return void
	 */
	public function invalidateControl($snippet = NULL)
	{
		$this->invalidSnippets[$snippet] = TRUE;
	}


	/**
	 * Allows control or its snippet to not repaint.
	 * @param  string
	 * @return void
	 */
	public function validateControl($snippet = NULL)
	{
		if ($snippet === NULL) {
			$this->invalidSnippets = array();

		} else {
			unset($this->invalidSnippets[$snippet]);
		}
	}


	/**
	 * Is required to repaint the control or its snippet?
	 * @param  string  snippet name
	 * @return bool
	 */
	public function isControlInvalid($snippet = NULL)
	{
		if ($snippet === NULL) {
			if (count($this->invalidSnippets) > 0) {
				return TRUE;

			} else {
				$queue = array($this);
				do {
					foreach (array_shift($queue)->getComponents() as $component) {
						if ($component instanceof IRenderable) {
							if ($component->isControlInvalid()) {
								// $this->invalidSnippets['__child'] = TRUE; // as cache
								return TRUE;
							}

						} elseif ($component instanceof Nette\ComponentModel\IContainer) {
							$queue[] = $component;
						}
					}
				} while ($queue);

				return FALSE;
			}

		} else {
			return isset($this->invalidSnippets[NULL]) || isset($this->invalidSnippets[$snippet]);
		}
	}


	/**
	 * Returns snippet HTML ID.
	 * @param  string  snippet name
	 * @return string
	 */
	public function getSnippetId($name = NULL)
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}

}
