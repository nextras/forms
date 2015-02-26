<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Tomas Votruba (http://tomasvotruba.cz)
 */

namespace Nextras\Forms\DI;

use Nette;
use Nette\DI\CompilerExtension;
use Nette\Forms\Container;
use Nette\PhpGenerator\ClassType;
use Nextras\Forms\Controls;


class FormsExtension extends CompilerExtension
{

	public function beforeCompile()
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		$builder->getDefinition('nette.latteFactory')
			->addSetup('Nextras\Forms\Bridges\Latte\Macros\BS3InputMacros::install(?->getCompiler())', array('@self'));
	}


	public function afterCompile(ClassType $class)
	{
		$init = $class->getMethods()['initialize'];
		$init->addBody(__CLASS__ . '::registerControls();');
	}


	public static function registerControls()
	{
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
	}

}
