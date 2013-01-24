## Nextras\Forms

List of components:
- **OptionList** - option control rendered as radio list
- **MultiOptionList** - multiple option control rendered as checkbox list
- **DatePicker** - date picker, represented by DateTime object
- **DateTimePicker** - date & time picker, represented by DateTime object

## Installation

The best way to install is using [Composer](http://getcomposer.org/):

```sh
$ composer require nextras/forms
```

## Documentation

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
```
