<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     David Grudl (http://davidgrudl.com)
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Utils\Html;



/**
 * Set of radio button controls.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 *
 * @property   array $items
 */
class RadioList extends Nette\Forms\Controls\BaseControl
{
	/** @var array */
	protected $items = array();



	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'radio';
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Returns selected radio value.
	 * @param  bool
	 * @return mixed
	 */
	public function getValue($raw = FALSE)
	{
		return is_scalar($this->value) && ($raw || isset($this->items[$this->value])) ? $this->value : NULL;
	}



	/**
	 * Has been any radio button selected?
	 * @return bool
	 */
	public function isFilled()
	{
		return $this->getValue() !== NULL;
	}



	/**
	 * Sets options from which to choose.
	 * @param  array
	 * @return RadioList  provides a fluent interface
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
		return $this;
	}



	/**
	 * Returns options from which to choose.
	 * @return array
	 */
	final public function getItems()
	{
		return $this->items;
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @param mixed $key  Specify a key if you want to render just a single checkbox
	 * @return Nette\Utils\Html
	 */
	public function getControl($key = NULL)
	{
		if ($key === NULL) {
			$container = Html::el();

		} elseif (!isset($this->items[$key])) {
			return NULL;
		}

		$control = parent::getControl();

		$rules = iterator_to_array($this->rules);
		foreach ($rules as $i => $rule) {
			if ($rule->operation === Nette\Forms\Form::FILLED)
				unset($rules[$i]);
		}
		$rules = self::exportRules($rules);
		$rules = substr(PHP_VERSION_ID >= 50400 ? json_encode($rules, JSON_UNESCAPED_UNICODE) : json_encode($rules), 1, -1);
		$rules = preg_replace('#"([a-z0-9_]+)":#i', '$1:', $rules);
		$rules = preg_replace('#(?<!\\\\)"(?!:[^a-z])([^\\\\\',]*)"#i', "'$1'", $rules);
		$control->data('nette-rules', $rules ? $rules : NULL);

		$id = $control->id;
		$counter = -1;
		$value = $this->value === NULL ? NULL : (string) $this->getValue();

		foreach ($this->items as $k => $val) {
			$counter++;
			if ($key !== NULL && $key != $k) continue; // intentionally ==

			$label = Nette\Utils\Html::el('label');

			$control->id = $label->for = $id . '-' . $counter;
			$control->checked = (string) $k === $value;
			$control->value = $k;
			unset($control->required);
			if ($key !== NULL) {
				return $control;
			}

			$label->class[] = 'radio';
			$label->add($control);
			$label->add($val instanceof Nette\Utils\Html ? $val : $this->translate($val));
			$container->add((string) $label);
		}

		return $container;
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return void
	 */
	public function getLabel($key = NULL, $caption = NULL)
	{
		$label = parent::getLabel();
		if ($key !== NULL) {
			$label = clone $label;
			$label->setText($this->items[$key]);
			$counter = -1;
			foreach ($this->items as $k => $val) {
				$counter++;
				if ($key != $k) continue; // intentionally ==
				break;
			}
			$label->for .= '-' . $counter;
			return $label;
		} else {
			$label = parent::getLabel($caption);
			$label->for = NULL;
			return $label;
		}

	}

}
