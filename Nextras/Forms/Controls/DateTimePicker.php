<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * Copyright (c) 2012 Jan Skrasek (http://jan.skrasek.com)
 *
 * @license    MIT
 * @link       https://github.com/nextras
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
		$this->setClassName('datetime');
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
		if (!is_string($value)) {
			return parent::setValue($value);
		}

		if (preg_match($value, '#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})(?:[. -] *(?P<yyyy>\d{4})?)?(?: *[ \-@] *(?P<hh>\d{1,2}):(?P<ii>\d{1,2}))?$#', $matches)) {
			$dd = $matches['dd'];
			$mm = $matches['mm'];
			$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

			$hh = isset($matches['hh']) ? $matches['hh'] : 0;
			$ii = isset($matches['ii']) ? $matches['ii'] : 0;

			if (!($hh > -1 && $hh < 24 && $ii > -1 && $ii < 60)) {
				$hh = $ii = 0;
			}

			if (checkdate($mm, $dd, $yyyy)) {
				$value = date(self::W3C_DATETIME_FORMAT, mktime($hh, $ii, 0, $mm, $dd, $yyyy));
			} else {
				$value = NULL;
			}
		} else {
			$value = NULL;
		}

		if ($value !== NULL) {
			try {
				$value = Nette\DateTime::from($value); // clone DateTime when given
			} catch (\Exception $e) {
				$value = NULL;
			}
		}

		if ($value instanceof DateTime) {
			$this->rawValue = $value->format(self::W3C_DATETIME_FORMAT);
		}

		$this->value = $value;
		return $this;
	}

}
