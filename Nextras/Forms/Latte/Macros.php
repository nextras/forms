<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Latte;

use Nette;
use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;
use Nette\Latte\Macros\MacroSet;



/**
 * Extended form macors for comfortable IListControl rendering
 *
 * @author   Jan Skrasek
 */
class Macros extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('input', array($me, 'macroInput'));
		$me->addMacro('label', array($me, 'macroLabel'), '?></label><?php');
	}



	public function macroInput(MacroNode $node, PhpWriter $writer)
	{
		$input = $node->tokenizer->fetchWord();
		if (($pos = strpos($input, ':')) !== FALSE) {
			$key   = $writer->formatWord(substr($input, $pos + 1));
			$input = $writer->formatWord(substr($input, 0, $pos));
			return $writer->write('echo $_form[%raw]->getControlItem(%raw)->addAttributes(%node.array)', $input, $key);
		} else {
			$input = $writer->formatWord($input);
			return $writer->write('echo $_form[%raw]->getControl()->addAttributes(%node.array)', $input);
		}
	}



	public function macroLabel(MacroNode $node, PhpWriter $writer)
	{
		$node->isEmpty = substr($node->args, -1) === '/';
		!$node->isEmpty ?: $node->setArgs(substr($node->args, 0, -1));

		$input = $node->tokenizer->fetchWord();
		if (($pos = strpos($input, ':')) !== FALSE) {
			$key   = $writer->formatWord(substr($input, $pos + 1));
			$input = $writer->formatWord(substr($input, 0, $pos));
			$code  = $writer->write('echo $_form[%raw]->getLabelItem(%raw)->addAttributes(%node.array)', $input, $key);
		} else {
			$input = $writer->formatWord($input);
			$code  = $writer->write('echo $_form[%raw]->getLabel()->addAttributes(%node.array)', $input);
		}

		if (!$node->isEmpty) {
			return $code . '->startTag()';
		} else {
			return $code;
		}
	}

}
