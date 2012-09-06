<?php

/**
 * This file was part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


namespace Nextras\Forms\Controls;

use Nette,
	Nette\Utils\Html;



/**
 * Set of radio button controls.
 *
 * @author     David Grudl
 * @author     Jan Skrasek
 *
 * @property   array $items
 * @property-read Nette\Utils\Html $separatorPrototype
 * @property-read Nette\Utils\Html $containerPrototype
 */
class RadioList extends Nette\Forms\Controls\BaseControl
{
	/** @var Nette\Utils\Html  separator element template */
	protected $separator;

	/** @var Nette\Utils\Html  container element template */
	protected $container;

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
		$this->container = Html::el();
		$this->separator = Html::el();
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
	 * Returns separator HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getSeparatorPrototype()
	{
		return $this->separator;
	}



	/**
	 * Returns container HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getContainerPrototype()
	{
		return $this->container;
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
			$container = clone $this->container;
			$separator = (string) $this->separator;

		} elseif (!isset($this->items[$key])) {
			return NULL;
		}

		$control = parent::getControl();
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
