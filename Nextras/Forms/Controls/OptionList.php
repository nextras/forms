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
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;


/**
 * Set of radion options.
 * @author David Grudl
 * @author Jan Skrasek
 *
 * @property       array $items
 * @property-read  Html  $container
 * @property-read  Html  $itemContainer
 */
class OptionList extends BaseControl implements \IteratorAggregate
{
	/** @var array */
	protected $items = array();

	/** @var BaseControl */
	protected $inputPrototype;

	/** @var Html control container */
	protected $container;

	/** @var Html item element container */
	protected $itemContainer;


	/**
	 * @param  string  label
	 * @param  array   options from which to choose
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label);
		$this->control->type = 'radio';
		$this->container = Html::el();
		$this->itemContainer = Html::el('div')->addClass('radio');
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}


	/**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
		$defaults = $this->getValue();
		$this->value = $this->getHttpData(Nette\Forms\Form::DATA_TEXT);
		if ($this->value !== NULL) {
			if (is_array($this->disabled) && isset($this->disabled[$this->value])) {
				$this->value = NULL;
			} else {
				$this->value = key(array($this->value => NULL));
			}
		}
		if ($defaults && is_array($this->disabled)) {
			$this->setDefaultValue($defaults);
		}
	}


	/**
	 * @inheritDoc
	 */
	public function setValue($value)
	{
		if ($value !== NULL && !isset($this->items[(string) $value])) {
			throw new Nette\InvalidArgumentException("Value '$value' is out of range of current items.");
		}
		$this->value = $value === NULL ? NULL : key(array((string) $value => NULL));
		return $this;
	}


	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue()
	{
		return isset($this->items[$this->value]) ? $this->value : NULL;
	}


	/**
	 * Returns selected radio value (not checked).
	 * @return mixed
	 */
	public function getRawValue()
	{
		return $this->value;
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
	 * @param  bool
	 * @return static  provides a fluent interface
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$useKeys) {
			$items = array_combine($items, $items);
		}
		$this->items = $items;
		return $this;
	}


	/**
	 * Returns options from which to choose.
	 * @return array
	 */
	public function getItems()
	{
		return $this->items;
	}


	public function setDefaultValue($value)
	{
		parent::setDefaultValue($value);
		if (is_array($this->disabled) && !is_array($value)) {
			$key = key(array($value => NULL));
			if (isset($this->disabled[$key]) && $this->value === NULL) {
				$this->value = $key;
			}
		}
		return $this;
	}


	public function setDisabled($value = TRUE)
	{
		if (!is_array($value)) {
			return parent::setDisabled($value);
		}
		parent::setDisabled(FALSE);
		$this->disabled = array_fill_keys($value, TRUE);
		if (isset($this->disabled[$this->value])) {
			$this->value = NULL;
		}
		return $this;
	}


	public function getContainerPrototype()
	{
		return $this->container;
	}


	public function getItemContainerPrototype()
	{
		return $this->itemContainer;
	}


	public function getIterator()
	{
		return new \ArrayIterator($this->items);
	}


	/**
	 * Generates control's HTML element.
	 *
	 * @param  string
	 * @return Html
	 */
	public function getControl($key = NULL)
	{
		if ($key !== NULL) {
			return $this->getControlPart($key);
		}

		$container = clone $this->container;
		foreach ($this->items as $key => $caption) {
			$label = $this->getLabelPart($key);
			$label->setHtml($this->getControlPart($key));
			$label->add($this->translate($caption));

			$container->add(
				$this->itemContainer->startTag() .
				(string) $label .
				$this->itemContainer->endTag()
			);
		}
		return $container;
	}


	public function getControlPart($key)
	{
		$key = key(array($key => NULL));

		$control = clone $this->getInputPrototype();
		$control->id .= '-' . $key;
		$control->checked = $this->getValue() === $key;
		$control->disabled = is_array($this->disabled) ? isset($this->disabled[$key]) : $this->disabled;
		$control->value = $key;
		return $control;
	}



	/** @deprecated */
	public function getControlItem($key)
	{
		return call_user_func_array([$this, 'getControlPart'], func_get_args());
	}


	/**
	 * Generates label's HTML element.
	 * @param  mixed
	 * @param  string
	 */
	public function getLabel($caption = NULL, $key = NULL)
	{
		if ($key !== NULL) {
			$label = $this->getLabelPart($key, $caption);
		} else {
			$label = parent::getLabel($caption);
			$label->for = NULL;
		}
		return $label;
	}


	public function getLabelPart($key, $caption = NULL)
	{
		$label = parent::getLabel($caption === NULL ? $this->items[$key] : $caption);
		$label->for .= '-' . $key;
		return $label;
	}


	/** @deprecated */
	public function getLabelItem($key, $caption = NULL)
	{
		return call_user_func_array([$this, 'getLabelPart'], func_get_args());
	}


	protected function getInputPrototype()
	{
		if ($this->inputPrototype) {
			return $this->inputPrototype;
		}

		return $this->inputPrototype = $this->createInputPrototype();
	}


	protected function createInputPrototype()
	{
		return parent::getControl();
	}

}
