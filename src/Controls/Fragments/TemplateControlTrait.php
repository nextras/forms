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


	/** @var bool */
	public $snippetMode;

	/** @var ITemplateFactory */
	private $templateFactory;

	/** @var ITemplate */
	private $template;

	/** @var array */
	private $invalidSnippets = [];


	/********************* template factory ****************d*g**/


	final public function setTemplateFactory(ITemplateFactory $templateFactory)
	{
		$this->templateFactory = $templateFactory;
		return $this;
	}



	final public function getTemplate(): ITemplate
	{
		if ($this->template === null) {
			$this->template = $this->createTemplate();
		}
		return $this->template;
	}


	protected function createTemplate(): ITemplate
	{
		$templateFactory = $this->templateFactory ?: $this->getPresenter()->getTemplateFactory();
		// edit start
		$template = $templateFactory->createTemplate(null);
		$template->control = $this;

		if ($template instanceof Template) {
			$presenter = $this->hasPresenter() ? $this->getPresenter() : null;
			$latte = $template->getLatte();
			$latte->addProvider('formsStack', [$this]);
			$latte->addProvider('uiControl', $this);
			$latte->addProvider('uiPresenter', $presenter);
			$latte->addProvider('snippetBridge', new SnippetBridge($this));
			if ($presenter) {
				$header = $presenter->getHttpResponse()->getHeader('Content-Security-Policy')
					?: $presenter->getHttpResponse()->getHeader('Content-Security-Policy-Report-Only');
			}
			$nonce = $presenter && preg_match('#\s\'nonce-([\w+/]+=*)\'#', (string) $header, $m) ? $m[1] : null;
			$latte->addProvider('uiNonce', $nonce);

			if ($presenter instanceof Presenter && $presenter->hasFlashSession()) {
				$id = $this->getParameterId('flash');
				$template->flashes = (array) $presenter->getFlashSession()->$id;
			}

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
	 */
	public function templatePrepareFilters(ITemplate $template): void
	{
	}


	/**
	 * Saves the message to template, that can be displayed after redirect.
	 */
	public function flashMessage($message, string $type = 'info'): \stdClass
	{
		$id = $this->getParameterId('flash');
		$messages = $this->getPresenter()->getFlashSession()->$id;
		$messages[] = $flash = (object) [
			'message' => $message,
			'type' => $type,
		];
		$this->getTemplate()->flashes = $messages;
		$this->getPresenter()->getFlashSession()->$id = $messages;
		return $flash;
	}


	/********************* rendering ****************d*g**/


	/**
	 * Forces control or its snippet to repaint.
	 */
	public function redrawControl(string $snippet = null, bool $redraw = true): void
	{
		if ($redraw) {
			$this->invalidSnippets[$snippet === null ? "\0" : $snippet] = true;

		} elseif ($snippet === null) {
			$this->invalidSnippets = [];

		} else {
			$this->invalidSnippets[$snippet] = false;
		}
	}


	/**
	 * Is required to repaint the control or its snippet?
	 */
	public function isControlInvalid(string $snippet = null): bool
	{
		if ($snippet === null) {
			if (count($this->invalidSnippets) > 0) {
				return true;

			} else {
				$queue = [$this];
				do {
					foreach (array_shift($queue)->getComponents() as $component) {
						if ($component instanceof IRenderable) {
							if ($component->isControlInvalid()) {
								// $this->invalidSnippets['__child'] = true; // as cache
								return true;
							}

						} elseif ($component instanceof Nette\ComponentModel\IContainer) {
							$queue[] = $component;
						}
					}
				} while ($queue);

				return false;
			}

		} else {
			return $this->invalidSnippets[$snippet] ?? isset($this->invalidSnippets["\0"]);
		}
	}


	/**
	 * Returns snippet HTML ID.
	 */
	public function getSnippetId(string $name): string
	{
		// HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
		return 'snippet-' . $this->getUniqueId() . '-' . $name;
	}
}
