<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 * and was partially extracted from Nette Framework itself due to the bad
 * Nette Form's object architecture.
 *
 * @license    New BSD License or the GNU General Public License (GPL) version 2 or 3.
 * @link       https://github.com/nextras/forms
 * @link       https://github.com/nette/application
 */

namespace Nextras\Forms\Controls\Fragments;

use Nette;
use Nette\Application\UI\IRenderable;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nextras\Forms\Bridges\Latte\SnippetBridge;


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
		$template = $this->getTemplate();
		$this->beforeRender();
		return (string) $template;
	}


	/**** Application\UI\Control **************************************************************************************/


	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = [];

	/** @var bool */
	public $snippetMode;


	/********************* template factory ****************d*g**/


	public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
	}


	/**
	 * @return ITemplate
	 */
	public function getTemplate()
	{
		if ($this->template === NULL) {
			$value = $this->createTemplate();
			if (!$value instanceof ITemplate && $value !== NULL) {
				$class2 = get_class($value); $class = get_class($this);
				throw new Nette\UnexpectedValueException("Object returned by $class::createTemplate() must be instance of Nette\\Application\\UI\\ITemplate, '$class2' given.");
			}
			$this->template = $value;
		}
		return $this->template;
	}


	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$templateFactory = $this->templateFactory ?: $this->getPresenter()->getTemplateFactory();
		// edit start
		$template = $templateFactory->createTemplate(null);
		$template->control = $this;

		if ($template instanceof Template) {
			$presenter = $this->getPresenter(false);
			$latte = $template->getLatte();
			$latte->addProvider('formsStack', [$this]);
			$latte->addProvider('uiControl', $this);
			$latte->addProvider('uiPresenter', $presenter);
			$latte->addProvider('snippetBridge', new SnippetBridge($this));
			$this->templatePrepareFilters($template);
		}

		if ($this->templateFile) {
			$template->setFile($this->templateFile);
		} else {
			$reflection = new \ReflectionClass(get_called_class());
			$template->setFile(dirname($reflection->getFileName()) . '/' . $reflection->getShortName() . '.latte');
		}

		return $template;
		// edit end
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  ITemplate
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
	}


	/********************* rendering ****************d*g**/


	/**
	 * Forces control or its snippet to repaint.
	 * @return void
	 */
	public function redrawControl($snippet = NULL, $redraw = TRUE)
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet === NULL ? "\0" : $snippet] = TRUE;

		} elseif ($snippet === NULL) {
			$this->invalidSnippets = [];

		} else {
			$this->invalidSnippets[$snippet] = FALSE;
		}
	}


	/** @deprecated */
	function invalidateControl($snippet = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use $this->redrawControl($snippet) instead.', E_USER_DEPRECATED);
		$this->redrawControl($snippet);
	}

	/** @deprecated */
	function validateControl($snippet = NULL)
	{
		trigger_error(__METHOD__ . '() is deprecated; use $this->redrawControl($snippet, FALSE) instead.', E_USER_DEPRECATED);
		$this->redrawControl($snippet, FALSE);
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
				$queue = [$this];
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

		} elseif (isset($this->invalidSnippets[$snippet])) {
			return $this->invalidSnippets[$snippet];
		} else {
			return isset($this->invalidSnippets["\0"]);
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
