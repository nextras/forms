<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Utils\Validators;
use Nette\Utils\Html;


/**
 * Set of checkbox options.
 *
 * @author Jan Skrasek
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
		$this->itemContainer = Html::el('div')->addClass('checkbox');
	}


	public function loadHttpData()
	{
		$defaults = $this->getValue();
		$this->setValue($this->getHttpData(Form::DATA_TEXT, '[]'));
		if ($this->value !== NULL) {
			foreach ($this->value as $value) {
				if (is_array($this->disabled) && isset($this->disabled[$value])) {
					$this->value = NULL;
					break;
				}
			}
		}
		if ($defaults && is_array($this->disabled)) {
			$this->setDefaultValue($defaults);
		}
	}


	public function setValue($values)
	{
		$this->value = array();
		foreach ((array) $values as $value) {
			$this->value[] = key(array($value => NULL));
		}
		return $this;
	}


	public function getValue()
	{
		$values = array();
		foreach ((array) $this->value as $value) {
			if (isset($this->items[$value])) {
				$values[] = $value;
			}
		}
		return $values;
	}


	public function isFilled()
	{
		return count($this->getValue()) > 0;
	}


	public function setDefaultValue($value)
	{
		parent::setDefaultValue($value);
		if (is_array($this->disabled)) {
			foreach ($value as $key) {
				$key = key(array($key => NULL));
				if (isset($this->disabled[$key]) && !in_array($key, $this->value, TRUE)) {
					$this->value[] = $key;
				}
			}
		}
		return $this;
	}


	public function setDisabled($value = TRUE)
	{
		if (!is_array($value)) {
			return parent::setDisabled($value);
		}
		parent::setDisabled(FALSE);
		$this->disabled = array_fill_keys($value, TRUE);
		foreach ($this->value as $value) {
			if (isset($this->disabled[$value])) {
				$this->value = NULL;
				break;
			}
		}
		return $this;
	}


	public function getControlPart($key)
	{
		$key = key(array($key => NULL));
		$control = clone $this->getInputPrototype();
		$control->id .= '-' . $key;
		$control->checked = in_array($key, $this->getValue(), TRUE);
		$control->disabled = is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled;
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
