<?php

/** @testcase */

namespace NextrasTests\Forms;

use Nextras\Forms\Bridges\Latte\Macros\BaseInputMacros;
use Tester\Assert;
use Tester\TestCase;
use Latte;

require_once __DIR__ . '/../bootstrap.php';


class MockBaseInputMacros extends BaseInputMacros {}


class BaseInputMactrosTest extends TestCase
{

	public function testLabel()
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		MockBaseInputMacros::install($latte->getCompiler());

		$result = $this->extract($latte->compile('{label foo /}'));
		Assert::same(
			'$_input = $_form["foo"];if ($_label = $_input->getLabel()) echo NextrasTests\Forms\MockBaseInputMacros::label($_label->addAttributes(array()), $_input, false) ; ',
			$result
		);

		$result = $this->extract($latte->compile('{label foo: /}'));
		Assert::same(
			'$_input = $_form["foo"];if ($_label = $_input->getLabelPart("")) echo NextrasTests\Forms\MockBaseInputMacros::label($_label->addAttributes(array()), $_input, true) ; ',
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
			'$_input = $_form["foo"];echo NextrasTests\Forms\MockBaseInputMacros::input($_input->getControl()->addAttributes(array()), $_input, false) ;',
			$result
		);

		$result = $this->extract($latte->compile('{input foo:}'));
		Assert::same(
			'$_input = $_form["foo"];echo NextrasTests\Forms\MockBaseInputMacros::input($_input->getControlPart("")->addAttributes(array()), $_input, true) ;',
			$result
		);
	}


	private function extract($content)
	{
		$needle = "//\n// main template\n//\n";
		$content = substr($content, strpos($content, $needle) + strlen($needle));
		return substr($content, 0, -3); // \n}}
	}

}


$test = new BaseInputMactrosTest();
$test->run();
