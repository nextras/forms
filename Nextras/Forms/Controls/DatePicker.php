<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Tvrdik (http://merxes.cz)
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Forms;
use DateTime;
use Nette\Forms\Controls\TextBase;


/**
 * Form control for selecting date.
 *
 * @author   Jan Tvrdik
 * @author   Jan Skrasek
 */
class DatePicker extends DateTimePickerPrototype
{
	/** @var string */
	protected $htmlFormat = self::W3C_DATE_FORMAT;

	/** @var string */
	protected $htmlType = 'date';


	/**
	 * Sets DatePicker value.
	 *
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

		return $value;
	}

}
