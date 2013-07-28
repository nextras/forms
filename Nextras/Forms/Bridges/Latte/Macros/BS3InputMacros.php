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
		if ($control instanceof TextBase || $control instanceof SelectBox) {
			$input->addClass('form-control');

		} elseif ($control instanceof Button && !$control instanceof ImageButton) {
			$input->setName('button');
			$input->add($input->value);
			$input->addClass('btn');
		}

		return $input;
	}

}
