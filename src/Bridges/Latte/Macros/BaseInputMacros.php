<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Bridges\Latte\Macros;

use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use Nette\Utils\Html;
use Nette\Forms\Controls\BaseControl;
use Nextras;


abstract class BaseInputMacros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('input', array($me, 'macroInput'));
		$me->addMacro('label', array($me, 'macroLabel'), array($me, 'macroLabelEnd'));
		return $me;
	}


	/**
	 * {label ...}
	 */
	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		$class = get_class($this);
		$words = $node->tokenizer->fetchWords();
		if (!$words) {
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word];'
				: '$_input = $_form[%0.word];'
			) . 'if ($_label = $_input->%1.raw) echo ' . $class . '::label($_label->addAttributes(%node.array), $_input, %2.var)',
			$name,
			$words ? ('getLabelPart(' . implode(', ', array_map(array($writer, 'formatWord'), $words)) . ')') : 'getLabel()',
			(bool) $words
		);
	}

	/**
	 * {/label}
	 */
	public function macroLabelEnd(MacroNode $node, PhpWriter $writer)
	{
		if ($node->content != NULL) {
			$node->openingCode = rtrim($node->openingCode, '?> ') . '->startTag() ?>';
			return $writer->write('if ($_label) echo $_label->endTag()');
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
			throw new CompileException("Missing name in {{$node->name}}.");
		}
		$name = array_shift($words);
		return $writer->write(
			($name[0] === '$'
				? '$_input = is_object(%0.word) ? %0.word : $_form[%0.word];'
				: '$_input = $_form[%0.word];'
			) . 'echo ' . $class . '::input($_input->%1.raw->addAttributes(%node.array), $_input, %2.var)',
			$name,
			$words ? 'getControlPart(' . implode(', ', array_map(array($writer, 'formatWord'), $words)) . ')' : 'getControl()',
			(bool) $words
		);
	}


	public static function label(Html $label, BaseControl $control, $isPart)
	{
		return $label;
	}


	public static function input(Html $input, BaseControl $control, $isPart)
	{
		return $input;
	}

}
