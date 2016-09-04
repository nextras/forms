<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Controls;

use Nette;


/**
 * Form control for selecting date and time.
 */
class DateTimePicker extends DateTimePickerPrototype
{
	/** @var string */
	protected $htmlFormat = self::W3C_DATETIME_FORMAT;

	/** @var string */
	protected $htmlType = 'datetime-local';


	protected function getDefaultParser()
	{
		return function($value) {
			if (!preg_match('#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})(?:[. -] *(?P<yyyy>\d{4})?)?(?: *[ @-] *(?P<hh>\d{1,2})[:.](?P<ii>\d{1,2})(?:[:.](?P<ss>\d{1,2}))?)?$#', $value, $matches)) {
				return null;
			}

			$dd = $matches['dd'];
			$mm = $matches['mm'];
			$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

			$hh = isset($matches['hh']) ? $matches['hh'] : 0;
			$ii = isset($matches['ii']) ? $matches['ii'] : 0;
			$ss = isset($matches['ss']) ? $matches['ss'] : 0;

			if (!($hh >= 0 && $hh < 24 && $ii >= 0 && $ii <= 59 && $ss >= 0 && $ss <= 59)) {
				$hh = $ii = $ss = 0;
			}

			if (!checkdate($mm, $dd, $yyyy)) {
				return null;
			}

			$value = new Nette\Utils\DateTime;
			$value->setDate($yyyy, $mm, $dd);
			$value->setTime($hh, $ii, $ss);
			return $value;
		};
	}
}
