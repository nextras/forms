<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Bridges\NetteDI;

use Nette\DI\CompilerExtension;
use Nette\Forms\Container;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\ObjectMixin;
use Nextras\Forms\Controls;


class FormsExtension extends CompilerExtension
{
	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();
		$builder->getDefinition('nette.latteFactory')
			->addSetup('?->onCompile[] = function ($engine) { Nextras\Forms\Bridges\Latte\Macros\BS3InputMacros::install($engine->getCompiler()); }', ['@self']);
	}


	public function afterCompile(ClassType $class)
	{
		$init = $class->getMethods()['initialize'];
		$init->addBody(__CLASS__ . '::registerControls();');
	}


	public static function registerControls()
	{
		ObjectMixin::setExtensionMethod(Container::class, 'addDatePicker', function (Container $container, $name, $label = null) {
			return $container[$name] = new Controls\DatePicker($label);
		});
		ObjectMixin::setExtensionMethod(Container::class, 'addDateTimePicker', function (Container $container, $name, $label = null) {
			return $container[$name] = new Controls\DateTimePicker($label);
		});
		ObjectMixin::setExtensionMethod(Container::class, 'addTypeahead', function(Container $container, $name, $label = null, $callback = null) {
			return $container[$name] = new Controls\Typeahead($label, $callback);
		});
	}
}
