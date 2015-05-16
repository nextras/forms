<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Closure;
use DateTime;
use Nette;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\Rules;
use Nette\Utils\Html;
use Nette\Utils\Validators;


abstract class DateTimePickerPrototype extends TextBase
{
	/** @link http://dev.w3.org/html5/spec/common-microsyntaxes.html#valid-date-string */
	const W3C_DATE_FORMAT = 'Y-m-d';

	/** @link http://dev.w3.org/html5/spec/common-microsyntaxes.html#parse-a-local-date-and-time-string */
	const W3C_DATETIME_FORMAT = 'Y-m-d\TH:i:s';

	/** @var string */
	protected $htmlFormat;

	/** @var string */
	protected $htmlType;

	/** @var Closure[] */
	protected $parsers = [];


	/**
	 * Generates control's HTML element.
	 *
	 * @return Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->type = $this->htmlType;
		$control->addClass($this->htmlType);
		$control->{'data-nette-rules'} = NULL;

		list($min, $max) = $this->extractRangeRule($this->getRules());
		if ($min instanceof DateTime) {
			$control->min = $min->format($this->htmlFormat);
		}
		if ($max instanceof DateTime) {
			$control->max = $max->format($this->htmlFormat);
		}
		$value = $this->getValue();
		if ($value instanceof DateTime) {
			$control->value = $value->format($this->htmlFormat);
		}

		return $control;
	}


	public function setValue($value)
	{
		return parent::setValue($value instanceof DateTime ? $value->format($this->htmlFormat) : $value);
	}


	/**
	 * @return Nette\Utils\DateTime|NULL
	 */
	public function getValue()
	{
		if ($this->value instanceof DateTime) {
			// clone
			return Nette\Utils\DateTime::from($this->value);

		} elseif (is_int($this->value)) {
			// timestamp
			return Nette\Utils\DateTime::from($this->value);

		} elseif (empty($this->value)) {
			return NULL;

		} elseif (is_string($this->value)) {
			$parsers = $this->parsers;
			$parsers[] = $this->getDefaultParser();

			foreach ($parsers as $parser) {
				$value = $parser($this->value);
				if ($value instanceof DateTime) {
					return $value;
				}
			}

			try {
				// DateTime constructor throws Exception when invalid input given
				return Nette\Utils\DateTime::from($this->value);
			} catch (\Exception $e) {
				return NULL;
			}
		}

		return NULL;
	}


	public function addParser(Closure $parser)
	{
		$this->parsers[] = $parser;
		return $this;
	}


	abstract protected function getDefaultParser();


	/**
	 * @return string
	 */
	public function getHtmlFormat()
	{
		return $this->htmlFormat;
	}


	/**
	 * Sets HTML format
	 * @param string
	 */
	public function setHtmlFormat($format)
	{
		$this->htmlFormat = $format;
	}


	/**
	 * Finds minimum and maximum allowed dates.
	 *
	 * @return array 0 => DateTime|NULL $minDate, 1 => DateTime|NULL $maxDate
	 */
	protected function extractRangeRule(Rules $rules)
	{
		$controlMin = $controlMax = NULL;
		foreach ($rules as $rule) {
			$branch = property_exists($rule, 'branch') ? $rule->branch : $rule->subRules;
			if (!$branch) {
				$validator = property_exists($rule, 'validator') ? $rule->validator : $rule->operation;
				if ($validator === Form::RANGE && !$rule->isNegative) {
					$ruleMinMax = $rule->arg;
				}

			} elseif ($branch) {
				$validator = property_exists($rule, 'validator') ? $rule->validator : $rule->operation;
				if ($validator === Form::FILLED && !$rule->isNegative && $rule->control === $this) {
					$ruleMinMax = $this->extractRangeRule($branch);
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
