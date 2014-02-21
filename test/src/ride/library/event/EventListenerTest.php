<?php

namespace ride\library\event;

use ride\library\reflection\Callback;

use \PHPUnit_Framework_TestCase;

class EventListenerTest extends PHPUnit_Framework_TestCase {

    public function testConstruct() {
        $event = 'event';
        $callback = 'callback';
        $weight = 5;

        $listener = new EventListener($event, $callback, $weight);

        $this->assertEquals($event, $listener->getEvent());
        $this->assertEquals(new Callback($callback), $listener->getCallback());
        $this->assertEquals($weight, $listener->getWeight());
    }

    /**
     * @dataProvider providerConstructWithInvalidArgumentThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testConstructWithInvalidArgumentThrowsException($name, $callback, $weight) {
        new EventListener($name, $callback, $weight);
    }

    public function providerConstructWithInvalidArgumentThrowsException() {
        return array(
            array(array(), 'callback', null),
            array($this, 'callback', null),
            array('event', null, null),
            array('event', 0, null),
            array('event', 'callback', array()),
            array('event', 'callback', $this),
            array('event', 'callback', -500),
        );
    }

    public function testToString() {
        $event = 'event';

        $callback = 'function';
        $listener = new EventListener($event, $callback);
        $this->assertEquals('event function', (string) $listener);

        $callback = array('someClass', 'someMethod');
        $listener = new EventListener($event, $callback);
        $this->assertEquals('event someClass::someMethod', (string) $listener);

        $callback = array($this, 'someMethod');
        $listener = new EventListener($event, $callback);
        $this->assertEquals('event ride\\library\\event\\EventListenerTest->someMethod', (string) $listener);

        $callback = array('class' => $this, 'method' => 'someMethod');
        $listener = new EventListener($event, $callback);
        $this->assertEquals('event Array', (string) $listener);

        $callback = array($this, 'someMethod');
        $listener = new EventListener($event, $callback, 15);
        $this->assertEquals('event ride\\library\\event\\EventListenerTest->someMethod #15', (string) $listener);
    }

}