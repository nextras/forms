<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Bridges\Latte\Macros;

use Nette\Utils\Html;
use Nette\Forms\Controls\BaseControl;
use Nextras;


class BS3InputMacros extends BaseInputMacros
{

	public static function label(Html $label, BaseControl $control)
	{
		if ($label->getName() === 'label') {
			$label->addClass('control-label');
			
			if(self::isHorizontal($control)) {
				$label->addClass('col-sm-4');
			} elseif(self::isInline($control)) {
				$label->addClass('sr-only');
			}
			
			if($control->isRequired()) {
				$label->addClass('required');
			}
		}

		return $label;
	}


	public static function input(Html $input, BaseControl $control)
	{
		$name = $input->getName();
		if ($name === 'select' || $name === 'textarea' || ($name === 'input' && !in_array($input->type, array('radio', 'checkbox', 'file', 'hidden', 'range', 'image', 'submit', 'reset')))) {
			$input->addClass('form-control');
			
			if($control->getOption('help', false) ||  self::isHorizontal($control)) {
				
				$wraper = Html::el('div');
				$wraper->add($input);
				
				if(self::isHorizontal($control)) {
					$wraper->addClass('col-sm-8');
				}
				
				if(($help = $control->getOption('help'))) {
					$wraper->add(Html::el('span')->setHtml($help)->addClass('help-block'));	
				}
				
				$input = $wraper;
			}
		} elseif ($name === 'input' && ($input->type === 'submit' || $input->type === 'reset')) {
			$input->setName('button');
			$input->add($input->value);
			$input->addClass('btn');
		}

		return $input;
	}

	private static function isHorizontal(BaseControl $control) 
	{
		$classes = $control->form->getElementPrototype()->class;
		return array_key_exists('form-horizontal', (array) $classes);
	}
	
	private static function isInline(BaseControl $control) 
	{
		$classes = $control->form->getElementPrototype()->class;
		return array_key_exists('form-inline', (array) $classes);
	}

}
