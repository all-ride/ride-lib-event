<?php

namespace ride\library\event\loader\io;

/**
 * Interface to read event listener definitions from a data source
 */
interface EventListenerIO {

    /**
     * Reads all the event listeners from the data source
     * @return array Hierarchic array with the name of the event as key and an
     * array with EventListener instances as value
     */
    public function readEventListeners();

}