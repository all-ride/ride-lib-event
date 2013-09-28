<?php

namespace pallo\library\event\loader;

use pallo\library\event\EventManager;

/**
 * Interface for a lazy event loader
 */
interface EventLoader {

    /**
     * Loads the event listeners for the provided event.
     * @param string $event Name of the event
     * @param EventManager $eventManager Instance of the event manager
     * @return null
     */
    public function loadEventListeners($event, EventManager $eventManager);

}