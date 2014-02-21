<?php

namespace ride\library\event;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class GenericEventManagerTest extends PHPUnit_Framework_TestCase {

    private $executed;

    protected function getProperty($instance, $property) {
        $reflectionProperty = new ReflectionProperty(get_class($instance), $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($instance);
    }

    public function testConstruct() {
        $maxEventListeners = 10;

        $eventManager = new GenericEventManager();
        $this->assertEquals(GenericEventManager::DEFAULT_MAX_EVENT_LISTENERS, $this->getProperty($eventManager, 'maxEventListeners'));
        $this->assertNotNull($this->getProperty($eventManager, 'invoker'));

        $invoker = $this->getMock('ride\\library\\reflection\\Invoker');
        $eventManager = new GenericEventManager($invoker, $maxEventListeners);
        $this->assertEquals($maxEventListeners, $this->getProperty($eventManager, 'maxEventListeners'));
        $this->assertEquals($invoker, $this->getProperty($eventManager, 'invoker'));
    }

    /**
     * @dataProvider providerConstructWithInvalidMaxEventListenersThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testConstructWithInvalidMaxEventListenersThrowsException($maxEventListeners) {
        new GenericEventManager(null, $maxEventListeners);
    }

    public function providerConstructWithInvalidMaxEventListenersThrowsException() {
        return array(
            array(0),
            array(-15),
            array('test'),
            array($this),
        );
    }

    public function testEventLoader() {
        $eventManager = new GenericEventManager();

        $loader = $this->getMock('ride\\library\\event\\loader\\EventLoader', array('loadEventListeners'));
        $loader->expects($this->once())->method('loadEventListeners');

        $this->assertNull($eventManager->getEventLoader());

        $eventManager->setEventLoader($loader);

        $eventManager->hasEventListeners('event');
        $eventManager->hasEventListeners('event');

        $this->assertEquals($loader, $eventManager->getEventLoader());
    }

    public function testAddEventListener() {
        $eventManager = new GenericEventManager();
        $event = 'event';
        $callback = array('class', 'testRegisterEvent');

        $listener = $eventManager->addEventListener($event, $callback);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

        $eventManager->addEventListener($event, $callback);
        $eventManager->addEventListener($event, $callback);
        $eventManager->addEventListener($event, $callback);

        $eventManager->removeEventListener();

        $events = $this->getProperty($eventManager, 'events');
        $this->assertEquals(array(), $events);
    }

    /**
     * @expectedException ride\library\event\exception\EventException
     */
    public function testAddEventListenerThrowsExceptionWhenListenerLimitReached() {
        $eventManager = new GenericEventManager(null, 3);
        $event = 'event';
        $callback = array('class', 'testRegisterEvent');

        $eventManager->addEventListener($event, $callback);
        $eventManager->addEventListener($event, $callback);
        $eventManager->addEventListener($event, $callback);
    }

    /**
     * @dataProvider providerAddEventListenerWithInvalidNameThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testAddEventListenerWithInvalidNameThrowsException($name) {
        $eventManager = new GenericEventManager();
        $eventManager->addEventListener($name, array('instance', 'method'));
    }

    public function providerAddEventListenerWithInvalidNameThrowsException() {
        return array(
            array(null),
            array(''),
            array($this),
        );
    }

    /**
     * @expectedException ride\library\event\exception\EventException
     */
    public function testAddEventListenerWithExistingWeightThrowsException() {
        $eventManager = new GenericEventManager();
        $event = 'event';
        $callback = array('class', 'method');

        $eventManager->addEventListener($event, $callback, 20);
        $eventManager->addEventListener($event, $callback, 20);
    }

    /**
     * @dataProvider providerAddEventListenerWithInvalidWeightThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testAddEventListenerWithInvalidWeightThrowsException($weight) {
        $eventManager = new GenericEventManager();
        $eventManager->addEventListener('event', array('instance', 'method'), $weight);
    }

    public function providerAddEventListenerWithInvalidWeightThrowsException() {
        return array(
            array('test'),
            array($this),
            array(70000),
        );
    }

    public function testRemoveEventListener() {
        $eventManager = new GenericEventManager();
        $event = 'event';
        $callback = 'callback';

        // delete by weight
        $listener = $eventManager->addEventListener($event, $callback);
        $listener2 = $eventManager->addEventListener($event, $callback);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));
        $this->assertEquals(2, count($events[$event]));

        $result = $eventManager->removeEventListener($event, $listener->getWeight());

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue($result);
        $this->assertEquals(1, count($events[$event]));
        $this->assertFalse(in_array($listener, $events[$event]));

        // delete by callback
        $listener = $eventManager->addEventListener($event, $callback);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

        $result = $eventManager->removeEventListener($event, $callback);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertFalse(in_array($listener, $events[$event]));
        $this->assertTrue($result);

        // delete by listener
        $listener = $eventManager->addEventListener($event, $callback);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

        $result = $eventManager->removeEventListener(null, $listener);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertFalse(in_array($listener, $events[$event]));
        $this->assertTrue($result);

        // delete last listener of event
        $result = $eventManager->removeEventListener($event, $listener2);

        $events = $this->getProperty($eventManager, 'events');
        $this->assertTrue($result);
        $this->assertEquals(array(), $events);


        // delete unset listeners
        $this->assertFalse($eventManager->removeEventListener($event, 10));
        $this->assertFalse($eventManager->removeEventListener('event2', 10));
    }

    public function testRemoveEventListenerWithouthArgumentsClearsAllEventListeners() {
        $eventManager = new GenericEventManager();

        $event = 'event';
        $callback = array($this, 'testClearEventListeners');

        $eventManager->addEventListener($event, $callback);

        $eventManager->removeEventListener();

        $this->assertEquals(array(), $this->getProperty($eventManager, 'events'));
    }

    /**
     * @dataProvider providerEventWithInvalidEventThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testRemoveEventListenerWithInvalidEventThrowsException($event) {
        $eventManager = new GenericEventManager();
        $eventManager->removeEventListener($event);
    }

    public function testTriggerEventWithoutEvents() {
        $eventManager = new GenericEventManager();
        $eventManager->triggerEvent('test');
    }

    public function testTriggerEventWithEvents() {
        $eventManager = new GenericEventManager();

        $event = 'event';
        $this->executed = false;
        $callback = array($this, 'eventCallbackMethod');

        $eventManager->addEventListener($event, $callback);
        $eventManager->triggerEvent($event);

        $this->assertTrue($this->executed, 'TestEvent has not been called');
    }

    public function testTriggerEventWithArguments() {
        $eventManager = new GenericEventManager();

        $event = 'event';
        $this->executed = 0;
        $callback = array($this, 'eventCallbackMethodSum');

        $eventManager->addEventListener($event, $callback);
        $eventManager->triggerEvent($event, array('value' => 1));
        $eventManager->triggerEvent($event, array('value' => 2));

        $this->assertEquals(3, $this->executed);
    }

    public function testTriggerEventWithWeights() {
        $eventManager = new GenericEventManager();

        $event = 'event';
        $this->executed = 10;
        $callback1 = array($this, 'eventCallbackMethod');
        $callback2 = array($this, 'eventCallbackMethodSum');
        $callback3 = array($this, 'eventCallbackMethodMultiply');
        $callback4 = array($this, 'eventCallbackMethodSubstract');

        $eventManager->addEventListener($event, $callback3);
        $eventManager->addEventListener($event, $callback1, 20);
        $eventManager->addEventListener($event, $callback4, 99);
        $eventManager->addEventListener($event, $callback2, 10);
        $eventManager->triggerEvent($event, array('value' => 7));

        // 1: 10 + 7 = 17
        // 2: 7
        // 3: 7 * 7 = 49
        // 4: 49 - 7 = 42

        $this->assertEquals(42, $this->executed);
    }

    public function testTriggerEventWithPreventDefault() {
        $eventManager = new GenericEventManager();

        $event = 'event';
        $this->executed = 10;
        $callback1 = array($this, 'eventCallbackMethod');
        $callback2 = array($this, 'eventCallbackMethodSum');
        $callback3 = array($this, 'eventCallbackMethodMultiply');
        $callback4 = array($this, 'eventCallbackMethodSubstract');
        $callback5 = array($this, 'eventCallbackMethodPreventDefault');

        $eventManager->addEventListener($event, $callback3);
        $eventManager->addEventListener($event, $callback1, 20);
        $eventManager->addEventListener($event, $callback5, 70);
        $eventManager->addEventListener($event, $callback4, 99);
        $eventManager->addEventListener($event, $callback2, 10);
        $eventManager->triggerEvent($event, array('value' => 7));

        // 1: 10 + 7 = 17
        // 2: 7
        // 3: 7 * 7 = 49
        // 4: stop chain
        // 5: 49 - 7 = 42

        $this->assertEquals(49, $this->executed);
    }

    /**
     * @dataProvider providerEventWithInvalidEventThrowsException
     * @expectedException ride\library\event\exception\EventException
     */
    public function testTriggerEventWithInvalidEventThrowsException($event) {
        $eventManager = new GenericEventManager();
        $eventManager->triggerEvent($event);
    }

    public function providerEventWithInvalidEventThrowsException() {
        return array(
            array(''),
            array($this),
        );
    }

    public function eventCallbackMethod($event) {
       $this->executed = $event->getArgument('value', true);
    }

    public function eventCallbackMethodSum($event) {
       $this->executed += $event->getArgument('value', 0);
    }

    public function eventCallbackMethodSubstract($event) {
       $this->executed -= $event->getArgument('value', 0);
    }

    public function eventCallbackMethodMultiply($event) {
       $this->executed *= $event->getArgument('value', 0);
    }

    public function eventCallbackMethodPreventDefault($event) {
       $event->setPreventDefault();
    }

}