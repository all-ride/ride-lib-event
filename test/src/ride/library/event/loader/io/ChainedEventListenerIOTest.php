<?php

namespace ride\library\event\loader\io;

use ride\library\event\EventListener;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class ChainedEventLoaderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ride\libarary\event\loader\io\ChainedEventListenerIO
     */
    protected $eventListenerIO;

    protected function setUp() {
        $this->eventListenerIO = new ChainedEventListenerIO();
    }

    protected function getProperty($instance, $property) {
        $reflectionProperty = new ReflectionProperty(get_class($instance), $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($instance);
    }

    public function testAddAndRemove() {
        $io1 = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                    ->getMock();
        $io2 = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                    ->getMock();
        $io3 = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                    ->getMock();

        $this->eventListenerIO->addEventListenerIO($io1);
        $this->eventListenerIO->addEventListenerIO($io2);
        $this->eventListenerIO->addEventListenerIO($io3, true);

        $this->assertEquals(array($io3, $io1, $io2), $this->getProperty($this->eventListenerIO, 'eventListenerIOs'));

        $result = $this->eventListenerIO->removeEventListenerIO($io1);

        $this->assertTrue($result);
        $this->assertEquals(array($io3, 2 => $io2), $this->getProperty($this->eventListenerIO, 'eventListenerIOs'));

        $result = $this->eventListenerIO->removeEventListenerIO($io1);
        $this->assertFalse($result);
    }

    public function testLoadEventListeners() {
        $listener1 = new EventListener('event', 'callback');
        $listener2 = new EventListener('event', 'callback2');
        $listener3 = new EventListener('event1', 'callback');
        $listener4 = new EventListener('event', 'callback3');
    	$listener5 = new EventListener('event2', 'callback');

        $result1 = array(
            'event' => array(
            	$listener1,
            	$listener2,
            ),
            'event1' => array(
            	$listener3,
            ),
        );

        $result2 = array(
            'event' => array(
                $listener4,
            ),
            'event2' => array(
                $listener5,
            ),
        );

        $expected = array(
        	'event' => array(
                $listener1,
        	    $listener2,
        	    $listener4,
            ),
            'event1' => array(
        		$listener3,
        	),
            'event2' => array(
        		$listener5,
        	),
        );

        $io1 = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                    ->getMock();
        $io1->expects($this->once())->method('readEventListeners')->will($this->returnValue($result1));

        $io2 = $this->getMockBuilder('ride\\library\\event\\loader\\io\\EventListenerIO')
                    ->getMock();
        $io2->expects($this->once())->method('readEventListeners')->will($this->returnValue($result2));

        $this->eventListenerIO->addEventListenerIO($io1);
        $this->eventListenerIO->addEventListenerIO($io2);

        $result = $this->eventListenerIO->readEventListeners();

        $this->assertEquals($expected, $result);
    }

}
