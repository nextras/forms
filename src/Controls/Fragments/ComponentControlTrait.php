<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 * and was partially extracted from Nette Framework itself due to the bad
 * Nette Form's object architecture.
 *
 * @license    New BSD License or the GNU General Public License (GPL) version 2 or 3.
 * @link       https://github.com/nextras/forms
 * @link       https://github.com/nette/application
 */

namespace Nextras\Forms\Controls\Fragments;

use Nette;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\ComponentReflection;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Container;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\IContainer;
use Nette\ComponentModel\RecursiveComponentIterator;


/**
 * ComponentControlTrait
 * @author Jan Skrasek
 */
trait ComponentControlTrait
{
	/** @var \ReflectionMethod */
	private $_createRequestMethodReflection;

	/** @var \ReflectionMethod */
	private $_handleInvalidLinkMethodReflection;


	/**** ComponentModel\Container ************************************************************************************/


	/** @var IComponent[] */
	private $components = [];

	/** @var Container|null */
	private $cloning;


	/********************* interface IContainer ****************d*g**/


	/**
	 * Adds the component to the container.
	 * @return static
	 * @throws Nette\InvalidStateException
	 */
	public function addComponent(IComponent $component, ?string $name, string $insertBefore = null)
	{
		if ($name === null) {
			$name = $component->getName();
		}

		if (!preg_match('#^[a-zA-Z0-9_]+\z#', $name)) {
			throw new Nette\InvalidArgumentException("Component name must be non-empty alphanumeric string, '$name' given.");
		}

		if (isset($this->components[$name])) {
			throw new Nette\InvalidStateException("Component with name '$name' already exists.");
		}

		// check circular reference
		$obj = $this;
		do {
			if ($obj === $component) {
				throw new Nette\InvalidStateException("Circular reference detected while adding component '$name'.");
			}
			$obj = $obj->getParent();
		} while ($obj !== null);

		// user checking
		$this->validateChildComponent($component);

		if (isset($this->components[$insertBefore])) {
			$tmp = [];
			foreach ($this->components as $k => $v) {
				if ($k === $insertBefore) {
					$tmp[$name] = $component;
				}
				$tmp[$k] = $v;
			}
			$this->components = $tmp;
		} else {
			$this->components[$name] = $component;
		}

		try {
			$component->setParent($this, $name);
		} catch (\Throwable $e) {
			unset($this->components[$name]); // undo
			throw $e;
		}
		return $this;
	}


	/**
	 * Removes the component from the container.
	 */
	public function removeComponent(IComponent $component): void
	{
		$name = $component->getName();
		if (($this->components[$name] ?? null) !== $component) {
			throw new Nette\InvalidArgumentException("Component named '$name' is not located in this container.");
		}

		unset($this->components[$name]);
		$component->setParent(null);
	}


	/**
	 * Returns component specified by name or path.
	 * @param  bool  $throw  throw exception if component doesn't exist?
	 */
	final public function getComponent(string $name, bool $throw = true): ?IComponent
	{
		[$name] = $parts = explode(self::NAME_SEPARATOR, $name, 2);

		if (!isset($this->components[$name])) {
			if (!preg_match('#^[a-zA-Z0-9_]+\z#', $name)) {
				if ($throw) {
					throw new Nette\InvalidArgumentException("Component name must be non-empty alphanumeric string, '$name' given.");
				}
				return null;
			}

			$component = $this->createComponent($name);
			if ($component && !isset($this->components[$name])) {
				$this->addComponent($component, $name);
			}
		}

		$component = $this->components[$name] ?? null;
		if ($component !== null) {
			if (!isset($parts[1])) {
				return $component;

			} elseif ($component instanceof IContainer) {
				return $component->getComponent($parts[1], $throw);

			} elseif ($throw) {
				throw new Nette\InvalidArgumentException("Component with name '$name' is not container and cannot have '$parts[1]' component.");
			}

		} elseif ($throw) {
			$hint = Nette\Utils\ObjectHelpers::getSuggestion(array_merge(
				array_keys($this->components),
				array_map('lcfirst', preg_filter('#^createComponent([A-Z0-9].*)#', '$1', get_class_methods($this)))
			), $name);
			throw new Nette\InvalidArgumentException("Component with name '$name' does not exist" . ($hint ? ", did you mean '$hint'?" : '.'));
		}
		return null;
	}


	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 */
	protected function createComponent(string $name): ?IComponent
	{
		$ucname = ucfirst($name);
		$method = 'createComponent' . $ucname;
		if ($ucname !== $name && method_exists($this, $method) && (new \ReflectionMethod($this, $method))->getName() === $method) {
			$component = $this->$method($name);
			if (!$component instanceof IComponent && !isset($this->components[$name])) {
				$class = get_class($this);
				throw new Nette\UnexpectedValueException("Method $class::$method() did not return or create the desired component.");
			}
			return $component;
		}
		return null;
	}


	/**
	 * Iterates over descendants components.
	 */
	final public function getComponents(bool $deep = false, string $filterType = null): \Iterator
	{
		$iterator = new RecursiveComponentIterator($this->components);
		if ($deep) {
			$iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);
		}
		if ($filterType) {
			$iterator = new \CallbackFilterIterator($iterator, function ($item) use ($filterType) {
				return $item instanceof $filterType;
			});
		}
		return $iterator;
	}


	/**
	 * Descendant can override this method to disallow insert a child by throwing an Nette\InvalidStateException.
	 * @throws Nette\InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child): void
	{
	}


	/********************* cloneable, serializable ****************d*g**/


	/**
	 * Object cloning.
	 */
	public function __clone()
	{
		if ($this->components) {
			$oldMyself = reset($this->components)->getParent();
			$oldMyself->cloning = $this;
			foreach ($this->components as $name => $component) {
				$this->components[$name] = clone $component;
			}
			$oldMyself->cloning = null;
		}
		parent::__clone();
	}


	/**
	 * Is container cloning now?
	 * @internal
	 */
	final public function _isCloning(): ?self
	{
		return $this->cloning;
	}


	/**** Application\UI\Component ************************************************************************************/


	use Nette\ComponentModel\ArrayAccess;

	/** @var callable[]  function (Component $sender): void; Occurs when component is attached to presenter */
	public $onAnchor;

	/** @var array */
	protected $params = [];


	/**
	 * Returns the presenter where this component belongs to.
	 */
	public function getPresenter(): ?Presenter
	{
		if (func_num_args()) {
			trigger_error(__METHOD__ . '() parameter $throw is deprecated, use hasPresenter()', E_USER_DEPRECATED);
			$throw = func_get_arg(0);
		}
		return $this->lookup(Presenter::class, $throw ?? true);
	}


	/**
	 * Returns whether there is a presenter.
	 */
	public function hasPresenter(): bool
	{
		return (bool) $this->lookup(Presenter::class, false);
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 */
	public function getUniqueId(): string
	{
		return $this->lookupPath(Presenter::class, true);
	}


	protected function validateParent(Nette\ComponentModel\IContainer $parent): void
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class, function (Presenter $presenter): void {
			$this->loadState($presenter->popGlobalParameters($this->getUniqueId()));
			$this->onAnchor($this);
		});
	}


	/**
	 * Calls public method if exists.
	 * @return bool  does method exist?
	 */
	protected function tryCall(string $method, array $params): bool
	{
		$rc = $this->getReflection();
		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);
			if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
				$this->checkRequirements($rm);
				try {
					$args = $rc->combineArgs($rm, $params);
				} catch (Nette\InvalidArgumentException $e) {
					throw new Nette\Application\BadRequestException($e->getMessage());
				}
				$rm->invokeArgs($this, $args);
				return true;
			}
		}
		return false;
	}


	/**
	 * Checks for requirements such as authorization.
	 */
	public function checkRequirements($element): void
	{
	}


	/**
	 * Access to reflection.
	 */
	public static function getReflection(): ComponentReflection
	{
		return new ComponentReflection(get_called_class());
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Loads state informations.
	 */
	public function loadState(array $params): void
	{
		$reflection = $this->getReflection();
		foreach ($reflection->getPersistentParams() as $name => $meta) {
			if (isset($params[$name])) { // nulls are ignored
				$type = gettype($meta['def']);
				if (!$reflection->convertType($params[$name], $type)) {
					throw new Nette\Application\BadRequestException(sprintf(
						"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
						$name,
						$this instanceof Presenter ? 'presenter ' . $this->getName() : "component '{$this->getUniqueId()}'",
						$type === 'NULL' ? 'scalar' : $type,
						is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name])
					));
				}
				$this->$name = $params[$name];
			} else {
				$params[$name] = $this->$name;
			}
		}
		$this->params = $params;
	}


	/**
	 * Saves state informations for next request.
	 */
	public function saveState(array &$params): void
	{
		$this->getReflection()->saveState($this, $params);
	}


	/**
	 * Returns component param.
	 * @return mixed
	 */
	final public function getParameter(string $name, $default = null)
	{
		return $this->params[$name] ?? $default;
	}


	/**
	 * Returns component parameters.
	 */
	final public function getParameters(): array
	{
		return $this->params;
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the parameter.
	 */
	final public function getParameterId(string $name): string
	{
		$uid = $this->getUniqueId();
		return $uid === '' ? $name : $uid . self::NAME_SEPARATOR . $name;
	}


	/** @deprecated */
	final public function getParam($name = null, $default = null)
	{
		trigger_error(__METHOD__ . '() is deprecated; use getParameter() or getParameters() instead.', E_USER_DEPRECATED);
		return func_num_args() ? $this->getParameter($name, $default) : $this->getParameters();
	}


	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * Calls signal handler method.
	 * @throws BadSignalException if there is not handler method
	 */
	public function signalReceived(string $signal): void
	{
		if (!$this->tryCall($this->formatSignalMethod($signal), $this->params)) {
			$class = get_class($this);
			throw new BadSignalException("There is no handler for signal '$signal' in class $class.");
		}
	}


	/**
	 * Formats signal handler method name -> case sensitivity doesn't matter.
	 */
	public static function formatSignalMethod(string $signal): string
	{
		return 'handle' . $signal;
	}


	/********************* navigation ****************d*g**/


	/**
	 * Generates URL to presenter, action or signal.
	 * @param  string   $destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed  $args
	 * @throws InvalidLinkException
	 */
	public function link(string $destination, $args = []): string
	{
		if (!$this->_createRequestMethodReflection) {
			$this->_createRequestMethodReflection = new \ReflectionMethod(Presenter::class, 'createRequest');
			$this->_createRequestMethodReflection->setAccessible(true);
			$this->_handleInvalidLinkMethodReflection = new \ReflectionMethod(Presenter::class, 'handleInvalidLink');
			$this->_handleInvalidLinkMethodReflection->setAccessible(true);
		}

		try {
			$args = func_num_args() < 3 && is_array($args) ? $args : array_slice(func_get_args(), 1);
			return $this->_createRequestMethodReflection->invoke($this->getPresenter(), $this, $destination, $args, 'link');

		} catch (InvalidLinkException $e) {
			return $this->_handleInvalidLinkMethodReflection->invoke($this->getPresenter(), $e);
		}
	}
}
