<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Bridges\Latte\Macros;

use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\ImageButton;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextBase;
use Nette\Utils\Html;
use Nette\Forms\Controls\BaseControl;
use Nextras;



class BS3InputMacros extends BaseInputMacros
{

	public static function label(Html $label, BaseControl $control)
	{
		if ($label->getName() === 'label') {
			$label->addClass('control-label');
		}

		return $label;
	}



	public static function input(Html $input, BaseControl $control)
	{
		if ($input->getName() === 'select' || ($input->getName() === 'input' && !in_array($input->type, array('radio', 'checkbox', 'file', 'hidden', 'range')))) {
			$input->addClass('form-control');

		} elseif ($input->getName() === 'input' && $input->type !== 'image') {
			$input->setName('button');
			$input->add($input->value);
			$input->addClass('btn');
		}

		return $input;
	}

}
