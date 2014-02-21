<?php

namespace ride\library\event;

/**
 * Interface for a manager of dynamic events
 */
interface EventManager {

    /**
     * Adds a new event listener
     * @param string $event Name of the event
     * @param string|array|ride\library\reflection\Callback $callback Callback
     * of the event listener
     * @param string $weight Weight in the listener list
     * @return EventListener
     * @throws ride\library\event\exception\EventException when a invalid
     * argument has been provided
     * @throws ride\library\event\exception\EventException when the weight of
     * the event listener is already set
     */
    public function addEventListener($event, $callback, $weight = null);

    /**
     * Removes event listeners
     * @param string $event Name of the event
     * @param mixed $eventListener A integer to remove by weight, a callback
     * or a instance of EventListener
     * @return boolean True when a event has been removed, false otherwise
     */
    public function removeEventListener($event = null, $eventListener = null);

    /**
     * Checks if there are event listeners registered for the provided event
     * @param string $event Name of the event
     * @return boolean
     * @throws ride\library\event\exception\EventException when a invalid
     * event name has been provided
     */
    public function hasEventListeners($event);

    /**
     * Triggers the listeners of the provided event with the provided arguments
     * @param string $event Name of the event
     * @param array $arguments Array with the arguments for the event listener
     * @return null
     * @throws Exception when the provided event name is empty or invalid
     */
    public function triggerEvent($event, array $arguments = null);

}