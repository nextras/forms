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

	/** @var IComponent|NULL */
	private $cloning;


	/********************* interface IContainer ****************d*g**/


	/**
	 * Adds the specified component to the IContainer.
	 * @param  IComponent
	 * @param  string
	 * @param  string
	 * @return self
	 * @throws Nette\InvalidStateException
	 */
	public function addComponent(IComponent $component, $name, $insertBefore = NULL)
	{
		if ($name === NULL) {
			$name = $component->getName();
		}

		if (is_int($name)) {
			$name = (string) $name;

		} elseif (!is_string($name)) {
			throw new Nette\InvalidArgumentException(sprintf('Component name must be integer or string, %s given.', gettype($name)));

		} elseif (!preg_match('#^[a-zA-Z0-9_]+\z#', $name)) {
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
		} while ($obj !== NULL);

		// user checking
		$this->validateChildComponent($component);

		try {
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
			$component->setParent($this, $name);

		} catch (\Exception $e) {
			unset($this->components[$name]); // undo
			throw $e;
		}
		return $this;
	}


	/**
	 * Removes a component from the IContainer.
	 * @return void
	 */
	public function removeComponent(IComponent $component)
	{
		$name = $component->getName();
		if (!isset($this->components[$name]) || $this->components[$name] !== $component) {
			throw new Nette\InvalidArgumentException("Component named '$name' is not located in this container.");
		}

		unset($this->components[$name]);
		$component->setParent(NULL);
	}


	/**
	 * Returns component specified by name or path.
	 * @param  string
	 * @param  bool   throw exception if component doesn't exist?
	 * @return IComponent|NULL
	 */
	public function getComponent($name, $need = TRUE)
	{
		if (isset($this->components[$name])) {
			return $this->components[$name];
		}

		if (is_int($name)) {
			$name = (string) $name;

		} elseif (!is_string($name)) {
			throw new Nette\InvalidArgumentException(sprintf('Component name must be integer or string, %s given.', gettype($name)));

		} else {
			$a = strpos($name, self::NAME_SEPARATOR);
			if ($a !== FALSE) {
				$ext = (string) substr($name, $a + 1);
				$name = substr($name, 0, $a);
			}

			if ($name === '') {
				if ($need) {
					throw new Nette\InvalidArgumentException('Component or subcomponent name must not be empty string.');
				}
				return;
			}
		}

		if (!isset($this->components[$name])) {
			$component = $this->createComponent($name);
			if ($component) {
				if (!$component instanceof IComponent) {
					throw new Nette\UnexpectedValueException('Method createComponent() did not return Nette\ComponentModel\IComponent.');

				} elseif (!isset($this->components[$name])) {
					$this->addComponent($component, $name);
				}
			}
		}

		if (isset($this->components[$name])) {
			if (!isset($ext)) {
				return $this->components[$name];

			} elseif ($this->components[$name] instanceof IContainer) {
				return $this->components[$name]->getComponent($ext, $need);

			} elseif ($need) {
				throw new Nette\InvalidArgumentException("Component with name '$name' is not container and cannot have '$ext' component.");
			}

		} elseif ($need) {
			$hint = Nette\Utils\ObjectMixin::getSuggestion(array_merge(
				array_keys($this->components),
				array_map('lcfirst', preg_filter('#^createComponent([A-Z0-9].*)#', '$1', get_class_methods($this)))
			), $name);
			throw new Nette\InvalidArgumentException("Component with name '$name' does not exist" . ($hint ? ", did you mean '$hint'?" : '.'));
		}
	}


	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 * @param  string      component name
	 * @return IComponent  the created component (optionally)
	 */
	protected function createComponent($name)
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
	}


	/**
	 * Iterates over components.
	 * @param  bool    recursive?
	 * @param  string  class types filter
	 * @return \ArrayIterator
	 */
	public function getComponents($deep = FALSE, $filterType = NULL)
	{
		$iterator = new RecursiveComponentIterator($this->components);
		if ($deep) {
			$deep = $deep > 0 ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::CHILD_FIRST;
			$iterator = new \RecursiveIteratorIterator($iterator, $deep);
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
	 * @return void
	 * @throws Nette\InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child)
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
			$oldMyself->cloning = NULL;
		}
		parent::__clone();
	}


	/**
	 * Is container cloning now?
	 * @return NULL|IComponent
	 * @internal
	 */
	public function _isCloning()
	{
		return $this->cloning;
	}


	/**** Application\UI\Component ************************************************************************************/


	/** @var callable[]  function (self $sender); Occurs when component is attached to presenter */
	public $onAnchor;

	/** @var array */
	protected $params = [];


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup(Presenter::class, $need);
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath(Presenter::class, TRUE);
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$this->loadState($presenter->popGlobalParameters($this->getUniqueId()));
			$this->onAnchor($this);
		}
	}


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor(Presenter::class);
	}


	/**
	 * Calls public method if exists.
	 * @param  string
	 * @param  array
	 * @return bool  does method exist?
	 */
	protected function tryCall($method, array $params)
	{
		$rc = $this->getReflection();
		if ($rc->hasMethod($method)) {
			$rm = $rc->getMethod($method);
			if ($rm->isPublic() && !$rm->isAbstract() && !$rm->isStatic()) {
				$this->checkRequirements($rm);
				$rm->invokeArgs($this, $rc->combineArgs($rm, $params));
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * Checks for requirements such as authorization.
	 * @return void
	 */
	public function checkRequirements($element)
	{
	}


	/**
	 * Access to reflection.
	 * @return ComponentReflection
	 */
	public static function getReflection()
	{
		return new ComponentReflection(get_called_class());
	}


	/********************* interface IStatePersistent ****************d*g**/


	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		$reflection = $this->getReflection();
		foreach ($reflection->getPersistentParams() as $name => $meta) {
			if (isset($params[$name])) { // NULLs are ignored
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
	 * @param  array
	 * @param  ComponentReflection (internal, used by Presenter)
	 * @return void
	 */
	public function saveState(array & $params, $reflection = NULL)
	{
		$reflection = $reflection === NULL ? $this->getReflection() : $reflection;
		foreach ($reflection->getPersistentParams() as $name => $meta) {

			if (isset($params[$name])) {
				// injected value

			} elseif (array_key_exists($name, $params)) { // NULLs are skipped
				continue;

			} elseif ((!isset($meta['since']) || $this instanceof $meta['since']) && isset($this->$name)) {
				$params[$name] = $this->$name; // object property value

			} else {
				continue; // ignored parameter
			}

			$type = gettype($meta['def']);
			if (!ComponentReflection::convertType($params[$name], $type)) {
				throw new InvalidLinkException(sprintf(
					"Value passed to persistent parameter '%s' in %s must be %s, %s given.",
					$name,
					$this instanceof Presenter ? 'presenter ' . $this->getName() : "component '{$this->getUniqueId()}'",
					$type === 'NULL' ? 'scalar' : $type,
					is_object($params[$name]) ? get_class($params[$name]) : gettype($params[$name])
				));
			}

			if ($params[$name] === $meta['def'] || ($meta['def'] === NULL && $params[$name] === '')) {
				$params[$name] = NULL; // value transmit is unnecessary
			}
		}
	}


	/**
	 * Returns component param.
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getParameter($name, $default = NULL)
	{
		if (isset($this->params[$name])) {
			return $this->params[$name];

		} else {
			return $default;
		}
	}


	/**
	 * Returns component parameters.
	 * @return array
	 */
	public function getParameters()
	{
		return $this->params;
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the parameter.
	 * @param  string
	 * @return string
	 */
	public function getParameterId($name)
	{
		$uid = $this->getUniqueId();
		return $uid === '' ? $name : $uid . self::NAME_SEPARATOR . $name;
	}


	/** @deprecated */
	function getParam($name = NULL, $default = NULL)
	{
		//trigger_error(__METHOD__ . '() is deprecated; use getParameter() instead.', E_USER_DEPRECATED);
		return func_num_args() ? $this->getParameter($name, $default) : $this->getParameters();
	}


	/**
	 * Returns array of classes persistent parameters. They have public visibility and are non-static.
	 * This default implementation detects persistent parameters by annotation @persistent.
	 * @return array
	 */
	public static function getPersistentParams()
	{
		$rc = new \ReflectionClass(get_called_class());
		$params = [];
		foreach ($rc->getProperties(\ReflectionProperty::IS_PUBLIC) as $rp) {
			if (!$rp->isStatic() && ComponentReflection::parseAnnotation($rp, 'persistent')) {
				$params[] = $rp->getName();
			}
		}
		return $params;
	}


	/********************* interface ISignalReceiver ****************d*g**/


	/**
	 * Calls signal handler method.
	 * @param  string
	 * @return void
	 * @throws BadSignalException if there is not handler method
	 */
	public function signalReceived($signal)
	{
		if (!$this->tryCall($this->formatSignalMethod($signal), $this->params)) {
			$class = get_class($this);
			throw new BadSignalException("There is no handler for signal '$signal' in class $class.");
		}
	}


	/**
	 * Formats signal handler method name -> case sensitivity doesn't matter.
	 * @param  string
	 * @return string
	 */
	public static function formatSignalMethod($signal)
	{
		return $signal == NULL ? NULL : 'handle' . $signal; // intentionally ==
	}


	/********************* navigation ****************d*g**/


	/**
	 * Generates URL to presenter, action or signal.
	 * @param  string   destination in format "[//] [[[module:]presenter:]action | signal! | this] [#fragment]"
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = [])
	{
		// edit start
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
			$this->_handleInvalidLinkMethodReflection->invoke($e);
		}
		// edit end
	}


	/********************* interface \ArrayAccess ****************d*g**/


	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}


	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette\ComponentModel\IComponent
	 * @throws Nette\InvalidArgumentException
	 */
	public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}


	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}


	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}

}
