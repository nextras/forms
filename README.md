## Nextras\Forms

List of components:
- **DatePicker** - date picker, represented by DateTime object
- **DateTimePicker** - date & time picker, represented by DateTime object
- **CheckboxList** - multiple option control rendered as checkbox list 
- **RadioList** - enhanced Nette Framework control for better rendering

## Installation

The best way to install is using [Composer](http://getcomposer.org/):

```sh
$ composer require nextras/forms
```

## Documentation

Initialization in your `bootstrap.php`:

```
use Nette\Forms\Container;
use Nextras\Forms\Controls;

Container::extensionMethod('addCheckboxList', function (Container $container, $name, $label = NULL, array $items = NULL) {
	return $container[$name] = new Controls\CheckboxList($label, $items);
});
Container::extensionMethod('addRadioList', function (Container $container, $name, $label = NULL, array $items = NULL) {
	return $container[$name] = new Controls\RadioList($label, $items);
});
Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
	return $container[$name] = new Controls\DatePicker($label);
});
Container::extensionMethod('addDateTimePicker', function (Container $container, $name, $label = NULL) {
	return $container[$name] = new Controls\DateTimePicker($label);
});
```
