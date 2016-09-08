<?php

namespace ride\library\event\loader;

use ride\library\event\EventListener;
use ride\library\event\GenericEventManager;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class GenericEventLoaderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ride\libarary\event\loader\ChainedEventLoader
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

        $eventManager = $this->getMockBuilder('ride\\library\\event\\EventManager')
                             ->getMock();
        $eventManager->expects($this->once())->method('addEventListener')->with($this->equalTo($event), $this->equalTo($callback), $this->equalTo($weight));

        $io = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                   ->getMock();
        $io->expects($this->once())->method('readEventListeners')->will($this->returnValue($ioResult));

        $eventLoader = new GenericEventLoader($io);
        $eventLoader->loadEventListeners($event, $eventManager);
    }

}
