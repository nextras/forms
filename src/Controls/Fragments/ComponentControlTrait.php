<?php

/**
 * This file is part of the Nextras community extensions of Nette Framework
 *
 * @license    MIT
 * @link       https://github.com/nextras/forms
 * @author     Jan Skrasek
 */

namespace Nextras\Forms\Controls\Fragments;

use Nette;
use Nette\Application\UI\BadSignalException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\PresenterComponentReflection;
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

	/**** ComponentModel\Container ************************************************************************************/


	/** @var IComponent[] */
	private $components = array();

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
			throw new Nette\InvalidArgumentException("Component name must be integer or string, " . gettype($name) . " given.");

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
				$tmp = array();
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
	final public function getComponent($name, $need = TRUE)
	{
		if (is_int($name)) {
			$name = (string) $name;

		} elseif (!is_string($name)) {
			throw new Nette\InvalidArgumentException("Component name must be integer or string, " . gettype($name) . " given.");

		} else {
			$a = strpos($name, IComponent::NAME_SEPARATOR);
			if ($a !== FALSE) {
				$ext = (string) substr($name, $a + 1);
				$name = substr($name, 0, $a);
			}

			if ($name === '') {
				if ($need) {
					throw new Nette\InvalidArgumentException("Component or subcomponent name must not be empty string.");
				}
				return;
			}
		}

		if (!isset($this->components[$name])) {
			$component = $this->createComponent($name);
			if ($component instanceof IComponent && $component->getParent() === NULL) {
				$this->addComponent($component, $name);
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
			throw new Nette\InvalidArgumentException("Component with name '$name' does not exist.");
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
		if ($ucname !== $name && method_exists($this, $method) && $this->getReflection()->getMethod($method)->getName() === $method) {
			$component = $this->$method($name);
			if (!$component instanceof IComponent && !isset($this->components[$name])) {
				$class = get_class($this);
				throw new Nette\UnexpectedValueException("Method $class::$method() did not return or create the desired component.");
			}
			return $component;
		}
	}


	/**
	 * Iterates over a components.
	 * @param  bool    recursive?
	 * @param  string  class types filter
	 * @return \ArrayIterator
	 */
	final public function getComponents($deep = FALSE, $filterType = NULL)
	{
		$iterator = new RecursiveComponentIterator($this->components);
		if ($deep) {
			$deep = $deep > 0 ? \RecursiveIteratorIterator::SELF_FIRST : \RecursiveIteratorIterator::CHILD_FIRST;
			$iterator = new \RecursiveIteratorIterator($iterator, $deep);
		}
		if ($filterType) {
			$iterator = new Nette\Iterators\Filter($iterator, function($item) use ($filterType) {
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


	/**** Application\UI\PresenterComponent ****************************************************************************/


	/** @var array */
	protected $params = array();


	/**
	 * Returns the presenter where this component belongs to.
	 * @param  bool   throw exception if presenter doesn't exist?
	 * @return Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette\Application\UI\Presenter', $need);
	}


	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
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
			$this->params = $presenter->popGlobalParameters($this->getUniqueId());
		}
	}


	/**
	 * @return void
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);
		$this->monitor('Nette\Forms\Form');
		$this->monitor('Nette\Application\UI\Presenter');
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
	 * @return PresenterComponentReflection
	 */
	public static function getReflection()
	{
		return new PresenterComponentReflection(get_called_class());
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
	public function formatSignalMethod($signal)
	{
		return $signal == NULL ? NULL : 'handle' . $signal; // intentionally ==
	}


	/********************* navigation ****************d*g**/


	/**
	 * Generates URL to presenter, action or signal.
	 * @param  string   destination in format "[[module:]presenter:]action" or "signal!" or "this"
	 * @param  array|mixed
	 * @return string
	 * @throws InvalidLinkException
	 */
	public function link($destination, $args = array())
	{
		$args = is_array($args) ? $args : array_slice(func_get_args(), 1);
		if (!(isset($destination[0]) && $destination[0] === ':')) {
			$path = $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
			$a = strpos($destination, '//');
			if ($a !== FALSE) {
				$destination = substr($destination, 0, $a + 2) . $path . '-' . substr($destination, $a + 2);
			} else {
				$destination = $path . '-' . $destination;
			}
			$newArgs = [];
			foreach ($args as $key => $arg) {
				$newArgs[$path . '-' . $key] = $arg;
			}
			$args = $newArgs;
		}
		return $this->getPresenter()->link($destination, $args);
	}


	/********************* interface \ArrayAccess ****************d*g**/


	/**
	 * Adds the component to the container.
	 * @param  string  component name
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	final public function offsetSet($name, $component)
	{
		$this->addComponent($component, $name);
	}


	/**
	 * Returns component specified by name. Throws exception if component doesn't exist.
	 * @param  string  component name
	 * @return Nette\ComponentModel\IComponent
	 * @throws Nette\InvalidArgumentException
	 */
	final public function offsetGet($name)
	{
		return $this->getComponent($name, TRUE);
	}


	/**
	 * Does component specified by name exists?
	 * @param  string  component name
	 * @return bool
	 */
	final public function offsetExists($name)
	{
		return $this->getComponent($name, FALSE) !== NULL;
	}


	/**
	 * Removes component from the container.
	 * @param  string  component name
	 * @return void
	 */
	final public function offsetUnset($name)
	{
		$component = $this->getComponent($name, FALSE);
		if ($component !== NULL) {
			$this->removeComponent($component);
		}
	}

}
