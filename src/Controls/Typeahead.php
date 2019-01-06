<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Controls;

use Nette\Application\IPresenter;
use Nette\Application\UI\ISignalReceiver;
use Nette\Forms\Controls\TextInput;
use Nette\InvalidStateException;
use Nette\Utils\Html;
use Nextras\Forms\Controls\Fragments\ComponentControlTrait;


/**
 * Form control for autocomplete.
 */
class Typeahead extends TextInput implements ISignalReceiver
{
	use ComponentControlTrait;

	/** @var callable */
	protected $callback;


	public function __construct($caption = null, $callback = null)
	{
		parent::__construct($caption);
		$this->setCallback($callback);
		$this->monitor(IPresenter::class, function () {
			$this->control->{'data-typeahead-url'} = $this->link('autocomplete!', ['q' => '__QUERY_PLACEHOLDER__']);
		});
	}


	public function getControl(): Html
	{
		$control = parent::getControl();
		$control->addClass('typeahead');
		return $control;
	}


	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}


	public function handleAutocomplete(string $q)
	{
		if (!$this->callback) {
			throw new InvalidStateException('Undefined Typeahead callback.');
		}

		$out = call_user_func($this->callback, $q);
		$this->getPresenter()->sendJson($out);
	}
}
