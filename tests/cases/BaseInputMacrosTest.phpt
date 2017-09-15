<?php

/** @testcase */

namespace NextrasTests\Forms;

use Nextras\Forms\Bridges\Latte\Macros\BaseInputMacros;
use Tester\Assert;
use Tester\TestCase;
use Latte;

require_once __DIR__ . '/../bootstrap.php';


class MockBaseInputMacros extends BaseInputMacros {}


class BaseInputMacrosTest extends TestCase
{

	public function testLabel()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		MockBaseInputMacros::install($latte->getCompiler());

		$result = $this->extract($latte->compile('{label foo /}'));
		Assert::same(
			'$_input = $_form["foo"];if ($_label = $_input->getLabel()) echo NextrasTests\Forms\MockBaseInputMacros::label($_label->addAttributes([]), $_input, false);',
			$result
		);

		$result = $this->extract($latte->compile('{label foo: /}'));
		Assert::same(
			'$_input = $_form["foo"];if ($_label = $_input->getLabelPart("")) echo NextrasTests\Forms\MockBaseInputMacros::label($_label->addAttributes([]), $_input, true);',
			$result
		);
	}



	public function testInput()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		MockBaseInputMacros::install($latte->getCompiler());

		$result = $this->extract($latte->compile('{input foo}'));
		Assert::same(
			'$_input = $_form["foo"];echo NextrasTests\Forms\MockBaseInputMacros::input($_input->getControl()->addAttributes([]), $_input, false);',
			$result
		);

		$result = $this->extract($latte->compile('{input foo:}'));
		Assert::same(
			'$_input = $_form["foo"];echo NextrasTests\Forms\MockBaseInputMacros::input($_input->getControlPart("")->addAttributes([]), $_input, true);',
			$result
		);
	}


	private function extract($content)
	{
		$lines = explode("\n", $content);
		$lines = array_slice($lines, 11, -5);
		$lines = implode('', array_map('trim', $lines));
		return $lines;
	}

}


$test = new BaseInputMacrosTest();
$test->run();
