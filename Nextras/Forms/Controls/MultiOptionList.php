<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Utils\Html;
use Nette\Forms\Form;
use Nette\Forms\IControl;



/**
 * Set of checkbox options.
 *
 * @author     Jan Skrasek
 *
 * @property   array $items
 */
class MultiOptionList extends OptionList
{
	/** validator */
	const FILLED = ':listFilled';

	/** @var array */
	protected $value = array();



	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
	}



	public function setValue($values)
	{
		$this->value = array();
		foreach ((array) $values as $value) {
			if (isset($this->items[$value])) {
				$this->value[] = (string) $value;
			}
		}
		return $this;
	}



	public function isFilled()
	{
		return count($this->getValue()) > 0;
	}



	public function addRule($operation, $message = NULL, $arg = NULL)
	{
		return parent::addRule($operation === Form::FILLED ? static::FILLED : $operation, $message, $arg);
	}



	public function addCondition($operation, $value = NULL)
	{
		return parent::addCondition($operation === Form::FILLED ? static::FILLED : $operation, $value);
	}



	public function getControlItem($key)
	{
		$control = clone $this->getInputPrototype();
		$control->id .= '-' . $key;
		$control->checked = in_array((string) $key, $this->getValue(), TRUE);
		$control->value = $key;
		return $control;
	}



	protected function createInputPrototype()
	{
		$control = parent::createInputPrototype();
		$control->name .= '[]';
		return $control;
	}



	/********************* validation *******************/



	public static function validateListFilled(IControl $control)
	{
		return $control->isFilled();
	}

}
