<?php

/** @testcase */

namespace NextrasTests\Forms;

use DateTime;
use DateTimeImmutable;
use Nextras\Forms\Controls\DatePicker;
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

	public function testDateTimeReturn()
	{
		$dateTimePicker_emptyValue = new DateTimePicker;
		Assert::null($dateTimePicker_emptyValue->getValue());

		$dateTimePicker_stringValue = new DateTimePicker;
		$dateTimePicker_stringValue->setValue('2018-01-01 06:00:00');

		Assert::type(DateTimeImmutable::class, $dateTimePicker_stringValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 06:00:00'), $dateTimePicker_stringValue->getValue());

		$dateTimePicker_dateTimeValue = new DateTimePicker;
		$dateTimePicker_dateTimeValue->setValue(new DateTime('2018-01-01 06:00:00'));

		Assert::type(DateTimeImmutable::class, $dateTimePicker_dateTimeValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 06:00:00'), $dateTimePicker_dateTimeValue->getValue());

		$dateTimePicker_dateTimeImmutableValue = new DateTimePicker;
		$dateTimePicker_dateTimeImmutableValue->setValue(new DateTimeImmutable('2018-01-01 06:00:00'));

		Assert::type(DateTimeImmutable::class, $dateTimePicker_dateTimeImmutableValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 06:00:00'), $dateTimePicker_dateTimeImmutableValue->getValue());
	}

	public function testDateReturn()
	{
		$datePicker_emptyValue = new DatePicker;
		Assert::null($datePicker_emptyValue->getValue());

		$datePicker_stringValue = new DatePicker;
		$datePicker_stringValue->setValue('2018-01-01 06:00:00');

		Assert::type(DateTimeImmutable::class, $datePicker_stringValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 00:00:00'), $datePicker_stringValue->getValue());

		$datePicker_dateTimeValue = new DatePicker;
		$datePicker_dateTimeValue->setValue(new DateTime('2018-01-01 06:00:00'));

		Assert::type(DateTimeImmutable::class, $datePicker_dateTimeValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 00:00:00'), $datePicker_dateTimeValue->getValue());

		$datePicker_dateTimeImmutableValue = new DatePicker;
		$datePicker_dateTimeImmutableValue->setValue(new DateTimeImmutable('2018-01-01 06:00:00'));

		Assert::type(DateTimeImmutable::class, $datePicker_dateTimeImmutableValue->getValue());
		Assert::equal(new DateTimeImmutable('2018-01-01 00:00:00'), $datePicker_dateTimeImmutableValue->getValue());
	}
}

$test = new DateTimePickerTest;
$test->run();
