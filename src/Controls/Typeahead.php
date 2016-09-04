<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Forms;
use Nextras\Forms\Controls\Fragments\ComponentControlTrait;


/**
 * Form control for autocomplete.
 */
class Typeahead extends Forms\Controls\TextInput implements Nette\Application\UI\ISignalReceiver
{
	use ComponentControlTrait {
		attached as componentControlAttached;
	}

	/** @var Nette\Utils\Callback */
	protected $callback;


	public function __construct($caption = null, $callback = null)
	{
		parent::__construct($caption);
		$this->setCallback($callback);
	}


	public function getControl()
	{
		$control = parent::getControl();
		$control->addClass('typeahead');
		return $control;
	}


	public function setCallback($callback)
	{
		$this->callback = $callback;
	}


	public function handleAutocomplete($q)
	{
		if (!$this->callback) {
			throw new Nette\InvalidStateException('Undefined Typehad callback.');
		}

		$this->getPresenter()->sendJson(Nette\Utils\Callback::invokeArgs($this->callback, [$q]));
	}


	protected function attached($component)
	{
		parent::attached($component);
		$this->componentControlAttached($component);
		if ($component instanceof Nette\Application\IPresenter) {
			$this->control->{'data-typeahead-url'} = $this->link('autocomplete!', ['q' => '__QUERY_PLACEHOLDER__']);
		}
	}
}
