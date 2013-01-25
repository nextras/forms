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
 * @author   Jan Tvrdik
 * @author   Jan Skrasek
 */
class DatePicker extends Forms\Controls\BaseControl
{
	/** @link http://dev.w3.org/html5/spec/common-microsyntaxes.html#valid-date-string */
	const W3C_DATE_FORMAT = 'Y-m-d';

	/** @var DateTime|NULL internal date reprezentation */
	protected $value;



	/**
	 * Class constructor.
	 *
	 * @param  string
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'date';
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->addClass($control->type);
		unset($control->data['nette-rules']);
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
	 * @param  DateTime|int|string
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value instanceof DateTime) {

		} elseif (is_int($value)) { // timestamp

		} elseif (empty($value)) {
			$value = NULL;

		} elseif (is_string($value)) {
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
			try {
				// DateTime constructor throws Exception when invalid input given
				$value = Nette\DateTime::from($value); // clone DateTime when given
				$value->setTime(0, 0, 0); // unify user input to day start
			} catch (\Exception $e) {
				$value = NULL;
			}
		}

		$this->value = $value;
		return $this;
	}



	/**
	 * Does user enter anything?
	 *
	 * @return bool
	 */
	public static function validateFilled(Forms\IControl $control)
	{
		return $control->getValue() !== NULL;
	}



	/**
	 * Is entered values within allowed range?
	 *
	 * @return bool
	 */
	public static function validateRange(Forms\IControl $control, $range)
	{
		return Nette\Utils\Validators::isInRange($control->getValue(), $range);
	}



	/**
	 * Finds minimum and maximum allowed dates.
	 *
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
