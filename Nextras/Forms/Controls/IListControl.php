<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls;

use Nette\Forms\IControl;



interface IListControl extends IControl, \Traversable
{

	function getControlItem($key);

	function getLabelItem($key);

}
