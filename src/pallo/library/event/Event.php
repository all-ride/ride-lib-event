<?php

namespace pallo\library\event;

use pallo\library\event\exception\EventException;

/**
 * Definition of a event
 */
class Event {

    /**
     * Name of the event
     * @var string
     */
    protected $name;

    /**
     * Arguments for the event listener
     * @var array
     */
    protected $arguments;

    /**
     * Flag to stop the listeners invokation
     * @var boolean
     */
    protected $preventDefault;

    /**
     * Constructs a new event listener
     * @param string $event Name of the event
     * @param array $arguments Arguments for the event listener
     * @return null
     * @throws pallo\library\event\exception\EventException when the name is
     * empty or invalid
     */
    public function __construct($name, array $arguments = null) {
        $this->setName($name);

        $this->arguments = $arguments;
        $this->preventDefault = false;
    }

    /**
     * Gets a string representation of this event
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

    /**
     * Sets the event name
     * @param string $name Name of the event
     * @return null
     * @throws pallo\library\event\exception\EventException when the name is
     * empty or invalid
     */
    protected function setName($name) {
        if (!is_string($name) || $name == '') {
            throw new EventException('Could not set the name of the event: provided name is invalid or empty');
        }

        $this->name = $name;
    }

    /**
     * Gets the name of the event
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Sets an argument
     * @param string $name Name of the argument
     * @param mixed|null $value Value of the argument, null to remove the
     * argument
     * @return null
     */
    public function setArgument($name, $value = null) {
        if ($value === null) {
            if (isset($this->arguments[$name])) {
                unset($this->arguments[$name]);
            }

            if (!$this->arguments) {
                $this->arguments = null;
            }
        } else {
            if ($this->arguments === null) {
                $this->arguments = array($name => $value);
            } else {
                $this->arguments[$name] = $value;
            }
        }
    }

    /**
     * Gets an argument
     * @param string $name Name of the argument
     * @param mixed $default Default value to be returned when the argument is
     * not set
     * @return mixed Value of the argument or the provided default value if the
     * argument is not set
     */
    public function getArgument($name, $default = null) {
        if (!isset($this->arguments[$name])) {
            return $default;
        }

        return $this->arguments[$name];
    }

    /**
     * Gets all the arguments
     * @return array
     */
    public function getArguments() {
        if ($this->arguments === null) {
            return array();
        }

        return $this->arguments;
    }

    /**
     * Sets the flag to prevent listener invokation
     * @param boolean $flag True to stop the listener invokation
     * @return null
     */
    public function setPreventDefault($flag = true) {
        $this->preventDefault = $flag;
    }

    /**
     * Gets the flag to prevent listener invokation
     * @return boolean True to stop the listener invokation, false to continue
     */
    public function isPreventDefault() {
        return $this->preventDefault;
    }

}