<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 */

namespace Nextras\Forms\Bridges\NetteDI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
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
		$latteFactory = $builder->getDefinition('nette.latteFactory');

		// nette v3 compatibility
		if ($latteFactory instanceof FactoryDefinition) {
			$latteFactory = $latteFactory->getResultDefinition();
		}

		$latteFactory->addSetup('?->onCompile[] = function ($engine) { Nextras\Forms\Bridges\Latte\Macros\BS3InputMacros::install($engine->getCompiler()); }', ['@self']);
	}


	public function afterCompile(ClassType $class)
	{
		$init = $class->getMethods()['initialize'];
		$init->addBody(__CLASS__ . '::registerControls();');
	}


	public static function registerControls()
	{
		$extensionsMethod = [
			'addDatePicker' => function (Container $container, $name, $label = null) {
				return $container[$name] = new Controls\DatePicker($label);
			},
			'addDateTimePicker' => function (Container $container, $name, $label = null) {
				return $container[$name] = new Controls\DateTimePicker($label);
			},
			'addTypeahead' => function(Container $container, $name, $label = null, $callback = null) {
				return $container[$name] = new Controls\Typeahead($label, $callback);
			},
		];

		if (method_exists(Container::class, 'extensionMethod')) {
			// Nette v3 compatibility
			foreach ($extensionsMethod as $name => $callback) {
				Container::extensionMethod($name, $callback);
			}
		}
		else {
			foreach ($extensionsMethod as $name => $callback) {
				ObjectMixin::setExtensionMethod(Container::class, $name, $callback);
			}
		}
	}
}
