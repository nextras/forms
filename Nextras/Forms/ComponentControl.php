<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms;

use Nette;
use Nette\Application\UI\ISignalReceiver;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\BaseControl;
use Nextras\Forms\Controls\Fragments\TemplateControlTrait;


/**
 * Base form control with Nette Component model support.
 *
 * @author Jan Skrasek
 * @author Jan Tvrdík
 */
abstract class ComponentControl extends BaseControl implements ISignalReceiver, \ArrayAccess, IContainer
{
	use TemplateControlTrait;


	public function __construct($caption = NULL)
	{
		parent::__construct($caption);
		$this->control = Nette\Utils\Html::el('div');
	}

}
