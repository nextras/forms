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
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Validators;



/**
 * Set of checkbox options.
 *
 * @author     Jan Skrasek
 *
 * @property   array $items
 */
class MultiOptionList extends OptionList
{
	/** @var array */
	protected $value = array();



	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
	}



	public function setValue($values)
	{
		$this->value = $values;
		return $this;
	}



	public function getValue()
	{
		$values = array();
		foreach ((array) $this->value as $value) {
			if (isset($this->items[$value])) {
				$values[] = (string) $value;
			}
		}
		return $values;
	}



	public function isFilled()
	{
		return count($this->getValue()) > 0;
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
		unset($control->required);
		return $control;
	}



	/********************* validation *******************/



	public static function validateMinLength(IControl $control, $length)
	{
		return count($control->getValue()) >= $length;
	}



	public static function validateMaxLength(IControl $control, $length)
	{
		return count($control->getValue()) <= $length;
	}



	public static function validateLength(IControl $control, $range)
	{
		if (!is_array($range)) {
			$range = array($range, $range);
		}

		return Validators::isInRange(count($control->getValue()), $range);
	}

}
