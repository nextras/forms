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

	public static function label(Html $label, BaseControl $control, $isPart)
	{
		if ($label->getName() === 'label' && !$isPart) {
			$label->addClass('control-label');
		}

		return $label;
	}


	public static function input(Html $input, BaseControl $control, $isPart)
	{
		$name = $input->getName();
		if ($name === 'select' || $name === 'textarea' || ($name === 'input' && !in_array($input->type, array('radio', 'checkbox', 'file', 'hidden', 'range', 'image', 'submit', 'reset')))) {
			$input->addClass('form-control');

		} elseif ($name === 'input' && ($input->type === 'submit' || $input->type === 'reset')) {
			$input->setName('button');
			$input->add($input->value);
			$input->addClass('btn');
		}

		return $input;
	}

}
