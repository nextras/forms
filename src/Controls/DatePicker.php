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
 * Form control for selecting date.
 */
class DatePicker extends DateTimePickerPrototype
{
	/** @var string */
	protected $htmlFormat = self::W3C_DATE_FORMAT;

	/** @var string */
	protected $htmlType = 'date';


	protected function getDefaultParser()
	{
		return function($value) {
			if (!preg_match('#^(?P<dd>\d{1,2})[. -] *(?P<mm>\d{1,2})([. -] *(?P<yyyy>\d{4})?)?$#', $value, $matches)) {
				return null;
			}

			$dd = $matches['dd'];
			$mm = $matches['mm'];
			$yyyy = isset($matches['yyyy']) ? $matches['yyyy'] : date('Y');

			if (!checkdate($mm, $dd, $yyyy)) {
				return null;
			}

			$value = new Nette\Utils\DateTime;
			$value->setDate($yyyy, $mm, $dd);
			$value->setTime(0, 0, 0);
			return $value;
		};
	}
}
