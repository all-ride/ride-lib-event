<?php

namespace pallo\library\event;

use pallo\library\reflection\Callback;

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
	 * @dataProvider providerConstructWithInvalidNameThrowsException
	 * @expectedException pallo\library\event\exception\EventException
	 */
	public function testConstructWithInvalidNameThrowsException($name, $callback, $weight) {
		new EventListener($name, $callback, $weight);
	}

	public function providerConstructWithInvalidNameThrowsException() {
		return array(
			array(array(), 'callback', null),
			array($this, 'callback', null),
			array('event', 'callback', array()),
			array('event', 'callback', $this),
			array('event', 'callback', -500),
		);
	}

}