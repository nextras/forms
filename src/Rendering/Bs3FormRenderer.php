<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    New BSD License
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Rendering;

use Nette;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;


/**
 * FormRenderer for Bootstrap 3 framework.
 */
class Bs3FormRenderer extends DefaultFormRenderer
{
	/** @var Controls\Button */
	public $primaryButton = null;

	/** @var bool */
	private $controlsInit = false;


	public function __construct()
	{
		$this->wrappers['controls']['container'] = null;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class=col-sm-9';
		$this->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';
		$this->wrappers['error']['container'] = null;
		$this->wrappers['error']['item'] = 'div class="alert alert-danger"';
	}


	public function renderBegin()
	{
		$this->controlsInit();
		return parent::renderBegin();
	}


	public function renderEnd()
	{
		$this->controlsInit();
		return parent::renderEnd();
	}


	public function renderBody()
	{
		$this->controlsInit();
		return parent::renderBody();
	}


	public function renderControls($parent)
	{
		$this->controlsInit();
		return parent::renderControls($parent);
	}


	public function renderPair(Nette\Forms\IControl $control)
	{
		$this->controlsInit();
		return parent::renderPair($control);
	}


	public function renderPairMulti(array $controls)
	{
		$this->controlsInit();
		return parent::renderPairMulti($controls);
	}


	public function renderLabel(Nette\Forms\IControl $control)
	{
		$this->controlsInit();
		return parent::renderLabel($control);
	}


	public function renderControl(Nette\Forms\IControl $control)
	{
		$this->controlsInit();
		return parent::renderControl($control);
	}


	private function controlsInit()
	{
		if ($this->controlsInit) {
			return;
		}

		$this->controlsInit = true;
		$this->form->getElementPrototype()->addClass('form-horizontal');
		foreach ($this->form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$markAsPrimary = $control === $this->primaryButton || (!isset($this->primaryButton) && empty($usedPrimary) && $control->parent instanceof Form);
				if ($markAsPrimary) {
					$class = 'btn btn-primary';
					$usedPrimary = true;
				} else {
					$class = 'btn btn-default';
				}
				$control->getControlPrototype()->addClass($class);

			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');

			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				if ($control->getSeparatorPrototype()->getName() !== null) {
					$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
				} else {
					$control->getItemLabelPrototype()->addClass($control->getControlPrototype()->type . '-inline');
				}
			}
		}
	}
}
