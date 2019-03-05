<?php

namespace Nextras\Forms\Rendering;

use Nette;
use Nette\Forms\Controls;
use Nette\Forms\Form;
use Nette\Forms\Rendering\DefaultFormRenderer;

/**
 * FormRenderer for Bootstrap 4 framework.
 */
class Bs4FormRenderer extends DefaultFormRenderer
{
	/** @var Controls\Button */
	public $primaryButton;

	/** @var bool */
	private $controlsInit = false;

	/** @var string */
	private $layout;


	public function __construct($layout = FormLayout::HORIZONTAL)
	{
		$this->layout = $layout;

		$groupClasses = 'form-group';
		if ($layout === FormLayout::HORIZONTAL) {
			$groupClasses .= ' row';
		} elseif ($layout === FormLayout::INLINE) {
			$groupClasses .= ' mb-2 mr-sm-2';
		}

		$this->wrappers['controls']['container'] = null;
		$this->wrappers['pair']['container'] = 'div class="' . $groupClasses . '"';
		$this->wrappers['control']['container'] = $layout == FormLayout::HORIZONTAL ? 'div class=col-sm-9' : null;
		$this->wrappers['label']['container'] = $layout == FormLayout::HORIZONTAL ? 'div class="col-sm-3 col-form-label"' : null;
		$this->wrappers['control']['description'] = 'small class="form-text text-muted"';
		$this->wrappers['control']['errorcontainer'] = 'div class=invalid-feedback';
		$this->wrappers['control']['.error'] = 'is-invalid';
		$this->wrappers['control']['.file'] = 'form-file';
		$this->wrappers['error']['container'] = null;
		$this->wrappers['error']['item'] = 'div class="alert alert-danger" role=alert';

		if ($layout === FormLayout::INLINE) {
			$this->wrappers['group']['container'] = null;
			$this->wrappers['group']['label'] = 'h2';
		}
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

		if ($this->layout == FormLayout::INLINE) {
			$this->form->getElementPrototype()->addClass('form-inline');
		}

		foreach ($this->form->getControls() as $control) {
			// TODO: remove after https://github.com/nette/forms/pull/209 is available
			$control->getControlPrototype()->class($this->getValue('control .error'), $control->hasErrors());

			if ($this->layout === FormLayout::INLINE && !$control instanceof Controls\Checkbox) {
				$control->getLabelPrototype()->addClass('my-1')->addClass('mr-2');
			}

			if ($control instanceof Controls\Button) {
				$markAsPrimary = $control === $this->primaryButton || (!isset($this->primaryButton) && empty($usedPrimary) && $control->parent instanceof Form);
				if ($markAsPrimary) {
					$class = 'btn btn-primary';
					$usedPrimary = true;
				} else {
					$class = 'btn btn-secondary';
				}
				$control->getControlPrototype()->addClass($class);

			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');

			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getControlPrototype()->addClass('form-check-input');

				$control->getSeparatorPrototype()->setName('div')->addClass('form-check')->class('form-check-inline', $this->layout == FormLayout::INLINE);

				if ($control instanceof Controls\Checkbox) {
					$control->getLabelPrototype()->addClass('form-check-label');
				} else {
					$control->getItemLabelPrototype()->addClass('form-check-label');
				}
			}
		}
	}
}
