<?php

namespace ride\library\event\loader;

use ride\library\event\loader\io\EventListenerIO;
use ride\library\event\EventManager;

/**
 * Generic implementation of a event loader
 */
class GenericEventLoader implements EventLoader {

    /**
     * I/O implementation
     * @var ride\library\event\loader\io\EventListenerIO
     */
    protected $io;

    /**
     * Loaded event listeners
     * @var array
     */
    protected $eventListeners;

    /**
     * Constructs a new event loader
     * @param ride\library\event\loader\io\EventIO $io
     * @return null
     */
    public function __construct(EventListenerIO $io) {
        $this->io = $io;
        $this->eventListeners = false;
    }

    /**
     * Loads and registers the event listeners for the provided event
     * @param string $event Name of the event
     * @param EventManager $eventManager Instance of the event manager
     * @return null
     */
    public function loadEventListeners($event, EventManager $eventManager) {
        if ($this->eventListeners === false) {
            $this->eventListeners = $this->io->readEventListeners();
        }

        if (isset($this->eventListeners[$event])) {
            $this->registerEventListeners($event, $eventManager);

            unset($this->eventListeners[$event]);
        }
    }

    /**
     * Registers the event listeners for the provided
     * @param unknown_type $event
     * @param EventManager $eventManager
     */
    protected function registerEventListeners($event, EventManager $eventManager) {
        foreach ($this->eventListeners[$event] as $eventListener) {
            $callback = $this->processCallback($eventListener->getCallback());
            $weight = $eventListener->getWeight();

            $eventManager->addEventListener($event, $callback, $weight);
        }
    }

    /**
     * Hook to process the callback
     * @param mixed $callback Callback to process
     * @return array|string Processed callback
     */
    protected function processCallback($callback) {
        return $callback;
    }

}