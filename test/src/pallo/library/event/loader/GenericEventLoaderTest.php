<?php

namespace pallo\library\event\loader;

use pallo\library\event\EventListener;
use pallo\library\event\GenericEventManager;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class GenericEventLoaderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var pallo\libarary\event\loader\ChainedEventLoader
     */
    protected $eventLoader;

    public function testLoadEventListeners() {
        $event = 'event';
        $callback = 'callback';
        $weight = 10;

        $listener = new EventListener($event, $callback, $weight);

        $ioResult = array(
        	'event' => array(
        	   $listener,
            ),
        );

        $eventManager = $this->getMock('pallo\\library\\event\\EventManager');
        $eventManager->expects($this->once())->method('addEventListener')->with($this->equalTo($event), $this->equalTo($callback), $this->equalTo($weight));

        $io = $this->getMock('pallo\\library\\event\\loader\\io\\EventListenerIO');
        $io->expects($this->once())->method('readEventListeners')->will($this->returnValue($ioResult));

        $eventLoader = new GenericEventLoader($io);
        $eventLoader->loadEventListeners($event, $eventManager);
    }

}