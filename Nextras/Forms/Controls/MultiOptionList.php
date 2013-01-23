<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Nette;
use Nette\Utils\Html;



/**
 * Set of checkbox controls.
 *
 * @author     Jan Skrasek
 *
 * @property   array $items
 */
class MultiOptionList extends OptionList
{

	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);
		$this->control->type = 'checkbox';
	}



	public function setValue($values)
	{
		dump($values);
		$this->value = array();
		foreach ($values as $value) {
			if (isset($this->items[$value])) {
				$this->value[] = $value;
			}
		}
		return $this;
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



	protected function createInputPrototype()
	{
		$control = parent::createInputPrototype();
		$control->name .= '[]';
		return $control;
	}

}
