<?php

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
