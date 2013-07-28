<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Bridges\Latte\Macros;

use Nette\Latte;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;
use Nette\Utils\Html;
use Nette\Forms\Controls\BaseControl;
use Nextras;



abstract class BaseInputMacros extends Latte\Macros\MacroSet
{

	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('input', array($me, 'macroInput'));
		$me->addMacro('label', array($me, 'macroLabel'), array($me, 'macroLabelEnd'));
	}



	/**
	 * {label ...}
	 */
	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		$class = get_class($this);
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new Latte\CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word];'
				: '$_input = $_form[%0.word];'
			) . 'if ($_label = $_input->getLabel(%1.raw)) echo ' . $class . '::label($_label->addAttributes(%node.array), $_input)',
			$name,
			($words ? 'NULL, ' : '') . implode(', ', array_map(array($writer, 'formatWord'), $words))
		);
	}



	/**
	 * {/label}
	 */
	public function macroLabelEnd(MacroNode $node, PhpWriter $writer)
	{
		if ($node->content != NULL) {
			$node->openingCode = substr_replace($node->openingCode, '->startTag()', strrpos($node->openingCode, ')') + 1, 0);
			return $writer->write('?></label><?php');
		}
	}



	/**
	 * {input ...}
	 */
	public function macroInput(MacroNode $node, PhpWriter $writer)
	{
		$class = get_class($this);
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new Latte\CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word];'
				: '$_input = $_form[%0.word];'
			) . 'echo ' . $class . '::input($_input->getControl(%1.raw)->addAttributes(%node.array), $_input)',
			$name,
			implode(', ', array_map(array($writer, 'formatWord'), $words))
		);
	}



	public static function label(Html $label, BaseControl $control)
	{
		return $label;
	}



	public static function input(Html $input, BaseControl $control)
	{
		return $input;
	}

}
