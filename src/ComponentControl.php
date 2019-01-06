<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms;

use Nette\Application\UI\IRenderable;
use Nette\Application\UI\ISignalReceiver;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use Nextras\Forms\Controls\Fragments\TemplateControlTrait;


/**
 * Base form control with Nette Component model support.
 */
abstract class ComponentControl extends BaseControl implements ISignalReceiver, \ArrayAccess, IContainer, IRenderable
{
	use TemplateControlTrait;


	public function __construct($caption = null)
	{
		parent::__construct($caption);
		$this->control = Html::el('div');
	}
}
