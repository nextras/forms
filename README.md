Nextras\Forms
=============

[![Build Status](https://travis-ci.org/nextras/forms.svg?branch=master)](https://travis-ci.org/nextras/forms)
[![Downloads this Month](https://img.shields.io/packagist/dm/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Stable version](http://img.shields.io/packagist/v/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Code coverage](https://img.shields.io/coveralls/nextras/forms.svg?style=flat)](https://coveralls.io/r/nextras/forms)
[![HHVM Status](http://img.shields.io/hhvm/nextras/forms.svg?style=flat)](http://hhvm.h4cc.de/package/nextras/forms)

List of components:
- **OptionList** - option control rendered as radio list
- **MultiOptionList** - multiple option control rendered as checkbox list
- **DatePicker** - date picker, represented by DateTime object
- **DateTimePicker** - date & time picker, represented by DateTime object
- **Tyheahead** - the best autocomplete for your forms
- **BS3InputMacros** - input macros for Bootstrap 3 (adds some css classes)

### Installation

The best way to install is using [Composer](http://getcomposer.org/):

```sh
$ composer require nextras/forms
```

For Date(Time)Picker we recommend use [DateTime Picker](http://www.malot.fr/bootstrap-datetimepicker/) for Bootstrap.
See JS init script.

### Documentation

Initialization in your `bootstrap.php`:

```php
use Nette\Forms\Container;
use Nextras\Forms\Controls;

Container::extensionMethod('addOptionList', function (Container $container, $name, $label = NULL, array $items = NULL) {
	return $container[$name] = new Controls\OptionList($label, $items);
});
Container::extensionMethod('addMultiOptionList', function (Container $container, $name, $label = NULL, array $items = NULL) {
	return $container[$name] = new Controls\MultiOptionList($label, $items);
});
Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
	return $container[$name] = new Controls\DatePicker($label);
});
Container::extensionMethod('addDateTimePicker', function (Container $container, $name, $label = NULL) {
	return $container[$name] = new Controls\DateTimePicker($label);
});
Container::extensionMethod('addTypeahead', function(Container $container, $name, $label = NULL, $callback = NULL) {
	return $container[$name] = new Controls\Typeahead($label, $callback);
});
```

Register your Bootstrap 3 macros in `config.neon`:
```neon
latte:
	macros:
		- Nextras\Forms\Bridges\Latte\Macros\BS3InputMacros
```

Render IListControls as you wish:
````php
$form->addMultiOptionList('list1', 'Pick your interests', ['a', 'b', 'c'])
     ->addRule($form::FILLED, 'You must pick some interest.');

$form->addMultiOptionList('list2', 'Pick your interests', ['d', 'e', 'f'])
	 ->addRule($form::LENGTH, 'You must pick just 1 or 2 interests.', array(1, 2));
```
```html
{form example}
<table>
<tr>
	<th>{label list1 /}</th>
	<td>{input list1}</td>
</tr>
<tr>
	<th>{label list2 /}</th>
	<td>
	{foreach $form['list2']->items as $key => $label}
		{label list2:$key}
			{input list2:$key}
			{$label}
		{/label}
	{/foreach}
	</td>
</tr>
</table>
{/form}
```
