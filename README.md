Nextras Forms
=============

[![Build Status](https://travis-ci.org/nextras/forms.svg?branch=master)](https://travis-ci.org/nextras/forms)
[![Downloads this Month](https://img.shields.io/packagist/dm/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Stable version](http://img.shields.io/packagist/v/nextras/forms.svg?style=flat)](https://packagist.org/packages/nextras/forms)
[![Code coverage](https://img.shields.io/coveralls/nextras/forms.svg?style=flat)](https://coveralls.io/r/nextras/forms)

List of components:
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
