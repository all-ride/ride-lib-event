<?php

namespace pallo\library\event;

use pallo\library\event\exception\EventException;
use pallo\library\reflection\Callback;

/**
 * Definition of a event listener
 */
class EventListener {

    /**
     * Name of the event
     * @var string
     */
    protected $event;

    /**
     * Callback of the listener
     * @var array|string
     */
    protected $callback;

    /**
     * Weight of the listener
     * @var integer
     */
    protected $weight;

    /**
     * Constructs a new event listener
     * @param string $event Name of the event
     * @param pallo\library\Callback|array|string $callback Callback to invoke
     * @param integer $weight Weight of the listener
     * @return null
     */
    public function __construct($event, $callback, $weight = null) {
    	$this->setEvent($event);
    	$this->setCallback($callback);
    	$this->setWeight($weight);
    }

    /**
     * Gets a string representation of this event
     * @return string
     */
    public function __toString() {
    	return $this->event . ' ' . $this->callback . ($this->weight ? ' #' . $this->weight : '');
    }


    /**
     * Sets the event name
     * @param string $event Name of the event
     * @return null
     * @throws pallo\library\event\exception\EventException when the name is
     * empty or invalid
     */
    protected function setEvent($event) {
    	if (!is_string($event) || $event == '') {
    		throw new EventException('Could not set the name of the event: provided name is invalid or empty');
    	}

    	$this->event = $event;
    }

    /**
     * Gets the name of the event
     * @return string
     */
    public function getEvent() {
    	return $this->event;
    }

    /**
     * Sets the callback of this listener
     * @param string|array $callback
     * @return null
     */
    protected function setCallback($callback) {
    	$this->callback = new Callback($callback);
    }

    /**
     * Gets the callback of this listener
     * @return pallo\library\Callback|array|string
     */
    public function getCallback() {
        return $this->callback;
    }

    /**
     * Sets the weight
     * @param integer $weight Weight of this listener
     * @return null
     * @throws pallo\library\event\exception\EventException when the provided
     * weight is invalid
     */
    public function setWeight($weight) {
    	if ($weight !== null && (!is_integer($weight) || $weight <= 0)) {
    		throw new EventException('Could not set the weight of the event: provided weight is not a positive number');
    	}

    	$this->weight = $weight;
    }

    /**
     * Gets the weight of this listener
     * @return integer|null
     */
    public function getWeight() {
        return $this->weight;
    }

}