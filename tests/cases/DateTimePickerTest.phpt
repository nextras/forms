<?php

/** @testcase */

namespace NextrasTests\Forms;

use Nextras\Forms\Controls\DateTimePicker;
use Tester\Assert;
use Tester\TestCase;
use Nette;

require_once __DIR__ . '/../bootstrap.php';

class DateTimePickerTest extends TestCase
{
	public function testNetteRule()
	{
		$dateTimePicker = new DateTimePicker;
		$dateTimePicker->setRequired();

		$form = new Nette\Forms\Form;
		$form->addComponent($dateTimePicker, 'dateTimePicker');

		$rule = new Nette\Forms\Rules($dateTimePicker);

		Assert::notSame(
			Nette\Forms\Helpers::exportRules($rule),
			$dateTimePicker->getControl()->getAttribute('data-nette-rules')
		);

		$rule->setRequired();

		Assert::same(
			Nette\Forms\Helpers::exportRules($rule),
			$dateTimePicker->getControl()->getAttribute('data-nette-rules')
		);
	}
}

$test = new DateTimePickerTest();
$test->run();
