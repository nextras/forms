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
 * Set of options.
 *
 * @author     Jan Skrasek
 *
 * @property   array $items
 */
class OptionList extends Nette\Forms\Controls\BaseControl
{
	/** @var array */
	protected $items = array();

	/** @var IControl */
	protected $inputPrototype;



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
	 * @inheritDoc
	 */
	public function setValue($value)
	{
		$this->value = is_scalar($value) && isset($this->items[$value]) ? $value : NULL;
		return $this;
	}



	/**
	 * Returns selected radio value.
	 * @return mixed
	 */
	public function getValue()
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
	public function getItems()
	{
		return $this->items;
	}



	/**
	 * Generates control's HTML element.
	 *
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$container = Html::el();
		foreach ($this->items as $key => $val) {
			$label = $this->getLabelItem($key);

			$label->class[] = $this->control->type;
			$text = $label->getText();
			$label->setText('');
			$label->add($this->getControlItem($key));
			$label->add($text);

			$container->add((string) $label);
		}
		return $container;
	}



	public function getControlItem($key)
	{
		$control = clone $this->getInputPrototype();
		$control->id .= '-' . $key;
		$control->checked = (string) $key === $this->getValue();
		$control->value = $key;
		return $control;
	}



	/**
	 * Generates label's HTML element.
	 * @param  string
	 * @return void
	 */
	public function getLabel($caption = NULL)
	{
		$label = parent::getLabel($caption);
		$label->for = NULL;
		return $label;
	}



	public function getLabelItem($key, $caption = NULL)
	{
		$label = parent::getLabel();
		$label->setText($caption ?: $this->translate($this->items[$key]));
		$label->for .= '-' . $key;
		return $label;
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
