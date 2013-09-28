<?php

namespace pallo\library\event;

use pallo\library\event\exception\EventException;
use pallo\library\reflection\Invoker;
use pallo\library\reflection\ReflectionHelper;

/**
 * Manager of dynamic events
 */
class GenericEventManager implements EventManager {

    /**
     * Default maximum number of event listeners for each event
     * @var integer
     */
    const DEFAULT_MAX_EVENT_LISTENERS = 100;

    /**
     * Instance of the invoker
     * @var pallo\library\reflection\Invoker
     */
    protected $invoker;

    /**
     * Maximum number of event listeners for each event
     * @var integer
     */
    protected $maxEventListeners;

    /**
     * Default weight for a new event
     * @var integer
     */
    protected $defaultWeight;

    /**
     * Array with the event name as key and a array with Event objects as value
     * @var array
     */
    protected $events;

    /**
     * Lazy event loader
     * @var pallo\library\event\EventLoader
     */
    protected $loader;

    /**
     * Array with events which have been lazy loaded
     * @var null|array
     */
    protected $loaded;

    /**
     * Constructs a new event manager
     * @param pallo\library\reflection\Invoker $invoker Invoker for callbacks
     * @param integer $maxEventListeners Maximum number of event listeners for
     * each event
     * @return null
     * @throws pallo\library\event\exception\EventException when the provided
     * maxEventListeners is not a positive number
     */
    public function __construct(Invoker $invoker = null, $maxEventListeners = self::DEFAULT_MAX_EVENT_LISTENERS) {
        $this->setMaxEventListeners($maxEventListeners);

        if (!$invoker) {
            $invoker = new ReflectionHelper();
        }

        $this->invoker = $invoker;
        $this->events = array();
        $this->loader = null;
        $this->loaded = null;
    }

    /**
     * Sets the maximum number of event listeners for each event
     * @param int $maxEventListeners
     * @return null
     * @throws pallo\library\event\exception\EventException when the provided
     * maxEventListeners is not a positive number
     */
    protected function setMaxEventListeners($maxEventListeners) {
        if (!is_integer($maxEventListeners) || $maxEventListeners <= 0) {
            throw new EventException('Could not set the maximum event listeners: provided maximum is not a positive number');
        }

        $this->maxEventListeners = $maxEventListeners;
        $this->defaultWeight = (int) floor($maxEventListeners / 2);
    }

    /**
     * Sets the lazy event loader
     * @param EventLoader $loader
     * @return null
     */
    public function setEventLoader(EventLoader $eventLoader) {
        $this->loader = $eventLoader;
        $this->loaded = array();
    }

    /**
     * Gets the lazy event loader
     * @return EventLoader
     */
    public function getEventLoader() {
        return $this->loader;
    }

    /**
     * Checks if there are event listeners registered for the provided event
     * @param string $event Name of the event
     * @return boolean
     * @throws pallo\library\event\exception\EventException when a invalid
     * event name has been provided
     */
    public function hasEventListeners($event) {
        if (!is_string($event) || $event == '') {
            throw new EventException('Could not check the event listeners: provided event name is invalid or empty');
        }

        if ($this->loader && !isset($this->loaded[$event])) {
            $this->loader->loadEventListeners($event, $this);

            $this->loaded[$event] = true;
        }

        return isset($this->events[$event]);
    }

    /**
     * Adds a new event listener
     * @param string $event Name of the event
     * @param string|array|pallo\library\reflection\Callback $callback Callback
     * of the event listener
     * @param string $weight Weight in the listener list
     * @return EventListener
     * @throws pallo\library\event\exception\EventException when a invalid
     * argument has been provided
     * @throws pallo\library\event\exception\EventException when the weight of
     * the event listener is invalid or already set
     */
    public function addEventListener($event, $callback, $weight = null) {
        $eventListener = new EventListener($event, $callback, $weight);

        // validate the weight value
        if ($weight === null) {
            $eventListener->setWeight($this->getNewWeight($event));
        } elseif ($weight >= $this->maxEventListeners) {
            throw new EventException('Could not register event listener for event ' . $event . ': provided weight is invalid. Try a value between 0 and ' . $this->maxEventListeners);
        } elseif (isset($this->events[$event][$weight])) {
            throw new EventException('Could not register event listener for event ' . $event . ': weight ' . $weight . ' is already set with listener ' . $this->events[$event][$weight]);
        }

        // add it
        if (!isset($this->events[$event])) {
            $this->events[$event] = array();
        }

        $this->events[$event][$eventListener->getWeight()] = $eventListener;

        // resort the event listeners by weight
        ksort($this->events[$event]);

        return $eventListener;
    }

    /**
     * Gets the new weight for the provided event
     * @param string $event Name of the event
     * @return integer Weight for a new event listener
     * @throws pallo\library\event\exception\EventException when no weight
     * could be found for the provided event
     */
    protected function getNewWeight($event) {
        $weight = $this->defaultWeight;

        do {
            if (!isset($this->events[$event][$weight])) {
                return $weight;
            }

            $weight++;
        } while ($weight < $this->maxEventListeners);

        throw new EventException('Could not get a new weight for event ' . $event . ': tried from ' . $this->defaultWeight . ' to ' . ($this->maxEventListeners - 1));
    }

    /**
     * Removes event listeners
     * @param string $event Name of the event
     * @param mixed $eventListener A integer to remove by weight, a callback
     * or a instance of EventListener
     * @return boolean True when a event has been removed, false otherwise
     */
    public function removeEventListener($event = null, $eventListener = null) {
        if ($event === null && $eventListener === null) {
            $this->events = array();

            return;
        }

        $isListener = $eventListener instanceof EventListener;
        if ($isListener) {
            $event = $eventListener->getEvent();
        }

        if (!$this->hasEventListeners($event)) {
            return false;
        }

        $result = false;

        if (!$isListener && is_scalar($eventListener) && isset($this->events[$event][$eventListener])) {
            unset($this->events[$event][$eventListener]);

            $result = true;
        }

        foreach ($this->events[$event] as $weight => $loopEventListener) {
            if ($isListener) {
                if ($loopEventListener == $eventListener) {
                    unset($this->events[$event][$weight]);

                    $result = true;

                    break;
                }
            } else {
                if ($loopEventListener->getCallback() == $eventListener) {
                    unset($this->events[$event][$weight]);

                    $result = true;

                    break;
                }
            }
        }

        if ($result && !$this->events[$event]) {
            unset($this->events[$event]);
        }

        return $result;
    }

    /**
     * Triggers the listeners of the provided event with the provided arguments
     * @param string $event Name of the event
     * @param array $arguments Array with the arguments for the event listener
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    public function triggerEvent($event, array $arguments = null) {
        if (!$this->hasEventListeners($event)) {
            return false;
        }

        $event = new Event($event, $arguments);

        foreach ($this->events[$event->getName()] as $eventListener) {
            $this->invoker->invoke($eventListener->getCallback(), array('event' => $event));

            if ($event->isPreventDefault()) {
                break;
            }
        }
    }

}