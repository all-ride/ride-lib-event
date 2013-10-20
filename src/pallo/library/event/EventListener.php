<?php

namespace pallo\library\event;

use pallo\library\event\exception\EventException;

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
        $callback = $this->callback;

        if (is_array($callback)) {
            if (isset($callback[0]) && isset($callback[1])) {
                if (is_string($callback[0])) {
                    $class = $callback[0] . '::';
                } else {
                    $class = get_class($callback[0]) . '->';
                }

                $callback = $class . $callback[1];
            } else {
                $callback = 'Array';
            }
        }

        return $this->event . ' ' . $callback . ($this->weight ? ' #' . $this->weight : '');
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
     * @param string|array|pallo\library\Callback $callback
     * @return null
     */
    protected function setCallback($callback) {
        if (empty($callback)) {
            throw new EventException('Could not set the callback of the event: provided callback is empty');
        }

        $this->callback = $callback;
    }

    /**
     * Gets the callback of this listener
     * @return string|array|pallo\library\Callback
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
        if ($weight === null) {
            $this->weight = null;

            return;
        }

        if (!is_numeric($weight) || $weight <= 0) {
            throw new EventException('Could not set the weight of the event: provided weight is not a positive number');
        }

        $this->weight = (integer) $weight;
    }

    /**
     * Gets the weight of this listener
     * @return integer|null
     */
    public function getWeight() {
        return $this->weight;
    }

}