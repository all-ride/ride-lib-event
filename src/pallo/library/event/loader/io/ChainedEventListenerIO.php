<?php

namespace pallo\library\event\loader\io;

/**
 * Chained implementation of a lazy event loader
 */
class ChainedEventListenerIO implements EventListenerIO {

    /**
     * Event loaders to chain
     * @var array
     */
    protected $eventListenerIOs = array();

    /**
     * Adds a event loader to the chain
     * @param EventListenerIO $eventLoader
     * @param boolean $prepend Set to true to add the event loader at the
     * beginning
     * @return null
     */
    public function addEventListenerIO(EventListenerIO $eventListenerIO, $prepend = false) {
        if ($prepend) {
            array_unshift($this->eventListenerIOs, $eventListenerIO);
        } else {
            $this->eventListenerIOs[] = $eventListenerIO;
        }
    }

    /**
     * Removes a event loader from the chain
     * @param EventListenerIO $eventLoader
     * @return boolean True if the provided event loader was found and removed,
     * false otherwise
     */
    public function removeEventListenerIO(EventListenerIO $eventListenerIO) {
        foreach ($this->eventListenerIOs as $index => $loopEventListenerIO) {
            if ($eventListenerIO === $loopEventListenerIO) {
                unset($this->eventListenerIOs[$index]);

                return true;
            }
        }

        return false;
    }

    /**
     * Reads all the event listeners from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with EventListener instances as value
     */
    public function readEventListeners() {
        $eventListeners = array();

        foreach ($this->eventListenerIOs as $eventListenerIO) {
            $readEventListeners = $eventListenerIO->readEventListeners();

            foreach ($readEventListeners as $event => $readEventListeners) {
                if (!isset($eventListeners[$event])) {
                    $eventListeners[$event] = array();
                }

                foreach ($readEventListeners as $eventListener) {
                    $eventListeners[$event][] = $eventListener;
                }
            }
        }

        return $eventListeners;
    }

}