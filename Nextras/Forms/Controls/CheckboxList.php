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
 * Set of checkbox controls.
 *
 * @author     David Grudl
 * @author     Jan Vlcek
 * @author     Jan Skrasek
 *
 * @property   array $items
 * @property-read Nette\Utils\Html $separatorPrototype
 * @property-read Nette\Utils\Html $containerPrototype
 */
class CheckboxList extends Nette\Forms\Controls\BaseControl
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
		$this->control->type = 'checkbox';
		$this->container = Html::el();
		$this->separator = Html::el();
		if ($items !== NULL) {
			$this->setItems($items);
		}
	}



	/**
	 * Returns selected radio value. NULL means nothing have been checked.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return is_array($this->value) ? $this->value : NULL;
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
	 *
	 * @param array $items
	 * @return CheckboxList  provides a fluent interface
	 */
	public function setItems(array $items)
	{
		$this->items = $items;
		return $this;
	}



	/**
	 * Returns options from which to choose.
	 *
	 * @return array
	 */
	public function getItems()
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
		$control->name .= '[]';
		$id = $control->id;
		$counter = -1;
		$values = $this->value === NULL ? NULL : (array) $this->getValue();

		foreach ($this->items as $k => $val) {
			$counter++;
			if ($key !== NULL && $key != $k) continue; // intentionally ==

			$label = Nette\Utils\Html::el('label');

			$control->id = $label->for = $id . '-' . $counter;
			$control->checked = (count($values) > 0) ? in_array($k, $values) : false;
			$control->value = $k;
			if ($key !== NULL) {
				return $control;
			}

			$label->class[] = 'checkbox';
			$label->add($control);
			$label->add($val instanceof Nette\Utils\Html ? $val : $this->translate($val));
			$container->add((string) $label . $separator);
		}

		return $container;
	}



	/**
	 * Generates label's HTML element.
	 *
	 * @return Html
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



	/**
	 * Filled validator: has been any checkbox checked?
	 *
	 * @param IControl $control
	 * @return bool
	 */
	public static function validateChecked(Nette\Forms\IControl $control)
	{
		return $control->getValue() !== NULL;
	}

}
