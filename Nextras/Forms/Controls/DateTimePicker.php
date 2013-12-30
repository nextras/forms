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
use DateTime;


/**
 * Form control for selecting date with time
 *
 * @author   Jan Skrasek
 */
class DateTimePicker extends DateTimePickerPrototype
{
	/** @var string */
	protected $htmlFormat = self::W3C_DATETIME_FORMAT;

	/** @var string */
	protected $htmlType = 'datetime-local';


	/**
	 * Sets DatePicker value.
	 *
	 * @author Jan Skrasek
	 * @param  DateTime|int|string
	 * @return self
	 */
	public function getValue()
	{
		$value = $this->value;
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

		return $value;
	}

}
