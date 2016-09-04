<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 * and was partially extracted from Nette Framework itself due to the bad
 * Nette Application's object architecture.
 *
 * @license    New BSD License or the GNU General Public License (GPL) version 2 or 3.
 * @link       https://github.com/nextras/forms
 * @link       https://github.com/nette/application
 */

namespace Nextras\Forms\Bridges\Latte;

use Nette;
use Latte\Runtime\ISnippetBridge;
use Nette\Application\UI\Control;
use Nette\Application\UI\IRenderable;


/**
 * @internal
 */
class SnippetBridge implements ISnippetBridge
{
	use Nette\SmartObject;

	/** @var Control */
	private $control;

	/** @var \stdClass|null */
	private $payload;


	public function __construct($control)
	{
		$this->control = $control;
	}


	public function isSnippetMode()
	{
		return $this->control->snippetMode;
	}


	public function setSnippetMode($snippetMode)
	{
		$this->control->snippetMode = $snippetMode;
	}


	public function needsRedraw($name)
	{
		return $this->control->isControlInvalid($name);
	}


	public function markRedrawn($name)
	{
		if ($name !== '') {
			$this->control->redrawControl($name, FALSE);
		}
	}


	public function getHtmlId($name)
	{
		return $this->control->getSnippetId($name);
	}


	public function addSnippet($name, $content)
	{
		if ($this->payload === NULL) {
			$this->payload = $this->control->getPresenter()->getPayload();
		}
		$this->payload->snippets[$this->control->getSnippetId($name)] = $content;
	}


	public function renderChildren()
	{
		$queue = [$this->control];
		do {
			foreach (array_shift($queue)->getComponents() as $child) {
				if ($child instanceof IRenderable) {
					if ($child->isControlInvalid()) {
						$child->snippetMode = TRUE;
						$child->render();
						$child->snippetMode = FALSE;
					}
				} elseif ($child instanceof Nette\ComponentModel\IContainer) {
					$queue[] = $child;
				}
			}
		} while ($queue);
	}

}
