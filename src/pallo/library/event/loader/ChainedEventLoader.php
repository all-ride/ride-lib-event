<?php

namespace pallo\library\event\loader;

use pallo\library\event\EventManager;

/**
 * Chained implementation of a lazy event loader
 */
class ChainedEventLoader implements EventLoader {

    /**
     * Event loaders to chain
     * @var array
     */
    protected $eventLoaders = array();

    /**
     * Adds a event loader to the chain
     * @param EventLoader $eventLoader
     * @param boolean $prepend Set to true to add the event loader at the
     * beginning
     * @return null
     */
    public function addEventLoader(EventLoader $eventLoader, $prepend = false) {
        if ($prepend) {
            array_unshift($this->eventLoaders, $eventLoader);
        } else {
            $this->eventLoaders[] = $eventLoader;
        }
    }

    /**
     * Removes a event loader from the chain
     * @param EventLoader $eventLoader
     * @return boolean True if the provided event loader was found and removed,
     * false otherwise
     */
    public function removeEventLoader(EventLoader $eventLoader) {
        foreach ($this->eventLoaders as $index => $loopEventLoader) {
            if ($eventLoader === $loopEventLoader) {
                unset($this->eventLoaders[$index]);

                return true;
            }
        }

        return false;
    }

    /**
     * Loads the event listeners for the provided event.
     * @param string $event Name of the event
     * @param EventManager $eventManager Instance of the event manager
     * @return null
     */
    public function loadEventListeners($event, EventManager $eventManager) {
        foreach ($this->eventLoaders as $eventLoader) {
            $eventLoader->loadEventListeners($event, $eventManager);
        }
    }

}