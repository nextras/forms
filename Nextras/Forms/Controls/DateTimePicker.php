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
use DateTime;



/**
 * Form control for selecting date with time
 *
 * @author   Jan Skrasek
 */
class DateTimePicker extends DatePicker
{

	/** @link http://dev.w3.org/html5/spec/common-microsyntaxes.html#parse-a-local-date-and-time-string */
	const W3C_DATETIME_FORMAT = 'Y-m-d\TH:i:s';



	/**
	 * Class constructor.
	 *
	 * @author Jan Skrasek
	 * @param  string
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'datetime-local';
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @author Jan Skrasek
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		list($min, $max) = $this->extractRangeRule($this->getRules());
		if ($min !== NULL) {
			$control->min = $min->format(self::W3C_DATETIME_FORMAT);
		}
		if ($max !== NULL) {
			$control->max = $max->format(self::W3C_DATETIME_FORMAT);
		}
		if ($this->value) {
			$control->value = $this->value->format(self::W3C_DATETIME_FORMAT);
		}
		return $control;
	}



	/**
	 * Sets DatePicker value.
	 *
	 * @author Jan Skrasek
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
			if (preg_match('#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})(?:[. -] *(?P<yyyy>\d{4})?)?(?: *[ -@] *(?P<hh>\d{1,2})[:.](?P<ii>\d{1,2}))?$#', $value, $matches)) {
				$dd = $matches['dd'];
				$mm = $matches['mm'];
				$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

				$hh = isset($matches['hh']) ? $matches['hh'] : 0;
				$ii = isset($matches['ii']) ? $matches['ii'] : 0;

				if (!($hh >= 0 && $hh < 24 && $ii >= 0 && $ii < 60)) {
					$hh = $ii = 0;
				}

				if (checkdate($mm, $dd, $yyyy)) {
					$value = date(self::W3C_DATETIME_FORMAT, mktime($hh, $ii, 0, $mm, $dd, $yyyy));
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
			} catch (\Exception $e) {
				$value = NULL;
			}
		}

		$this->value = $value;
		return $this;
	}

}
