<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Tvrdik (http://merxes.cz)
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Forms;
use DateTime;




/**
 * Form control for selecting date.
 *
 *  – compatible with jQuery UI DatePicker and HTML 5
 *  – works with DateTime
 *
 * @author   Jan Tvrdik
 * @version  2.3
 */
class DatePicker extends Forms\Controls\BaseControl
{
	/** @link http://dev.w3.org/html5/spec/common-microsyntaxes.html#valid-date-string */
	const W3C_DATE_FORMAT = 'Y-m-d';

	/** @var DateTime|NULL internal date reprezentation */
	protected $value;

	/** @var string value entered by user (unfiltered) */
	protected $rawValue;

	/** @var string class name */
	private $className = 'date';



	/**
	 * Class constructor.
	 *
	 * @author Jan Tvrdik
	 * @param  string
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'date';
	}



	/**
	 * Returns class name.
	 *
	 * @author Jan Tvrdik
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}



	/**
	 * Sets class name for input element.
	 *
	 * @author Jan Tvrdik
	 * @param  string
	 * @return self
	 */
	public function setClassName($className)
	{
		$this->className = $className;
		return $this;
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @author Jan Tvrdik
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->addClass($this->className);
		list($min, $max) = $this->extractRangeRule($this->getRules());
		if ($min !== NULL) {
			$control->min = $min->format(self::W3C_DATE_FORMAT);
		}
		if ($max !== NULL) {
			$control->max = $max->format(self::W3C_DATE_FORMAT);
		}
		if ($this->value) {
			$control->value = $this->value->format(self::W3C_DATE_FORMAT);
		}
		return $control;
	}



	/**
	 * Sets DatePicker value.
	 *
	 * @author Jan Tvrdik
	 * @param  DateTime|int|string
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value instanceof DateTime) {

		} elseif (is_int($value)) { // timestamp

		} elseif (empty($value)) {
			$rawValue = $value;
			$value = NULL;

		} elseif (is_string($value)) {
			$rawValue = $value;

			if (preg_match('#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})([. -] *(?P<yyyy>\d{4})?)?$#', $value, $matches)) {
				$dd = $matches['dd'];
				$mm = $matches['mm'];
				$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

				if (checkdate($mm, $dd, $yyyy)) {
					$value = "$yyyy-$mm-$dd";
				} else {
					$value = NULL;
				}
			}

		} else {
			throw new \InvalidArgumentException();
		}

		if ($value !== NULL) {
			// DateTime constructor throws Exception when invalid input given
			try {
				$value = Nette\DateTime::from($value); // clone DateTime when given
			} catch (\Exception $e) {
				$value = NULL;
			}
		}

		if (!isset($rawValue) && isset($value)) {
			$rawValue = $value->format(self::W3C_DATE_FORMAT);
		}

		$this->value = $value;
		$this->rawValue = $rawValue;

		return $this;
	}



	/**
	 * Returns unfiltered value.
	 *
	 * @author Jan Tvrdik
	 * @return string
	 */
	public function getRawValue()
	{
		return $this->rawValue;
	}



	/**
	 * Does user enter anything? (the value doesn't have to be valid)
	 *
	 * @author Jan Tvrdik
	 * @param  DatePicker
	 * @return bool
	 */
	public static function validateFilled(Forms\IControl $control)
	{
		if (!$control instanceof self) {
			throw new Nette\InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');
		}
		$rawValue = $control->rawValue;
		return !empty($rawValue);
	}



	/**
	 * Is entered value valid? (empty value is also valid!)
	 *
	 * @author Jan Tvrdik
	 * @param  DatePicker
	 * @return bool
	 */
	public static function validateValid(Forms\IControl $control)
	{
		if (!$control instanceof self) {
			throw new Nette\InvalidStateException('Unable to validate ' . get_class($control) . ' instance.');
		}
		$value = $control->value;
		return empty($control->rawValue) || $value instanceof DateTime;
	}



	/**
	 * Is entered values within allowed range?
	 *
	 * @author Jan Tvrdik
	 * @author David Grudl
	 * @param  DatePicker
	 * @param  array 0 => minDate, 1 => maxDate
	 * @return bool
	 */
	public static function validateRange(Forms\IControl $control, $range)
	{
		return Nette\Utils\Validators::isInRange($control->getValue(), $range);
	}



	/**
	 * Finds minimum and maximum allowed dates.
	 *
	 * @author Jan Tvrdik
	 * @param  Forms\Rules
	 * @return array 0 => DateTime|NULL $minDate, 1 => DateTime|NULL $maxDate
	 */
	protected function extractRangeRule(Forms\Rules $rules)
	{
		$controlMin = $controlMax = NULL;
		foreach ($rules as $rule) {
			if ($rule->type === Forms\Rule::VALIDATOR) {
				if ($rule->operation === Forms\Form::RANGE && !$rule->isNegative) {
					$ruleMinMax = $rule->arg;
				}

			} elseif ($rule->type === Forms\Rule::CONDITION) {
				if ($rule->operation === Forms\Form::FILLED && !$rule->isNegative && $rule->control === $this) {
					$ruleMinMax = $this->extractRangeRule($rule->subRules);
				}
			}

			if (isset($ruleMinMax)) {
				list($ruleMin, $ruleMax) = $ruleMinMax;
				if ($ruleMin !== NULL && ($controlMin === NULL || $ruleMin > $controlMin)) {
					$controlMin = $ruleMin;
				}
				if ($ruleMax !== NULL && ($controlMax === NULL || $ruleMax < $controlMax)) {
					$controlMax = $ruleMax;
				}
				$ruleMinMax = NULL;
			}
		}
		return array($controlMin, $controlMax);
	}

}
