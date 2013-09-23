<?php

namespace pallo\library\event;

use pallo\library\reflection\Callback;

use \PHPUnit_Framework_TestCase;
use \ReflectionProperty;

class EventManagerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var EventManager
     */
    private $eventManager;

    private $executed;

    protected function setUp() {
        $this->eventManager = new EventManager();
    }

    protected function getProperty($instance, $property) {
    	$reflectionProperty = new ReflectionProperty(get_class($instance), $property);
    	$reflectionProperty->setAccessible(true);

    	return $reflectionProperty->getValue($instance);
    }

    public function testConstructWithMaxEvents() {
        $maxEventListeners = 10;

        $eventManager = new EventManager($maxEventListeners);

        $this->assertEquals($maxEventListeners, $this->getProperty($eventManager, 'maxEventListeners'));
        $this->assertEquals(EventManager::DEFAULT_MAX_EVENT_LISTENERS, $this->getProperty($this->eventManager, 'maxEventListeners'));
    }

    /**
     * @dataProvider providerConstructWithInvalidMaxEventsThrowsException
     * @expectedException pallo\library\event\exception\EventException
     */
    public function testConstructWithInvalidMaxEventsThrowsException($maxEvents) {
		new EventManager($maxEvents);
    }

    public function providerConstructWithInvalidMaxEventsThrowsException() {
        return array(
            array(0),
            array(-15),
            array('test'),
            array($this),
        );
    }

    public function testEventLoader() {
    	$loader = $this->getMock('pallo\\library\\event\\EventLoader', array('loadEvents'));
    	$loaderCall = $loader->expects($this->once());
    	$loaderCall->method('loadEvents');

    	$this->assertNull($this->eventManager->getEventLoader());

    	$this->eventManager->setEventLoader($loader);

    	$this->eventManager->hasEventListeners('event');
    	$this->eventManager->hasEventListeners('event');

    	$this->assertEquals($loader, $this->eventManager->getEventLoader());
    }

    public function testRegisterEventListener() {
        $event = 'event';
        $callback = array('class', 'testRegisterEvent');

        $listener = $this->eventManager->registerEventListener($event, $callback);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->registerEventListener($event, $callback);

        $this->eventManager->clearEventListeners();

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertEquals(array(), $events);
    }

    /**
	 * @expectedException pallo\library\event\exception\EventException
     */
    public function testRegisterEventListenerThrowsExceptionWhenListenerLimitReached() {
        $event = 'event';
        $callback = array('class', 'testRegisterEvent');

        $this->eventManager = new EventManager(3);

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->registerEventListener($event, $callback);
    }

    /**
     * @dataProvider providerRegisterEventWithInvalidNameThrowsException
	 * @expectedException pallo\library\event\exception\EventException
     */
    public function testRegisterEventListenerWithInvalidNameThrowsException($name) {
		$this->eventManager->registerEventListener($name, array('instance', 'method'));
    }

    public function providerRegisterEventWithInvalidNameThrowsException() {
        return array(
            array(null),
            array(''),
            array($this),
        );
    }

    /**
     * @expectedException pallo\library\event\exception\EventException
     */
    public function testRegisterEventListenerWithExistingWeightThrowsException() {
        $event = 'event';
        $callback = array('instance', 'method');

        $this->eventManager->registerEventListener($event, $callback, 20);
        $this->eventManager->registerEventListener($event, $callback, 20);
    }

    /**
     * @dataProvider providerRegisterEventWithInvalidWeightThrowsException
     * @expectedException pallo\library\event\exception\EventException
     */
    public function testRegisterEventWithInvalidWeightThrowsException($weight) {
		$this->eventManager->registerEventListener('event', array('instance', 'method'), $weight);
    }

    public function providerRegisterEventWithInvalidWeightThrowsException() {
        return array(
            array('test'),
            array($this),
            array(70000),
        );
    }

    public function testUnregisterEventListener() {
    	$event = 'event';
    	$callback = 'callback';

    	// delete by weight
    	$listener = $this->eventManager->registerEventListener($event, $callback);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

    	$result = $this->eventManager->unregisterEventListener($listener->getWeight(), $event);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertFalse(in_array($listener, $events[$event]));
        $this->assertTrue($result);

        // delete by callback
        $listener = $this->eventManager->registerEventListener($event, $callback);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

    	$result = $this->eventManager->unregisterEventListener($callback, $event);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertFalse(in_array($listener, $events[$event]));
        $this->assertTrue($result);

        // delete by callback
        $listener = $this->eventManager->registerEventListener($event, $callback);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

    	$result = $this->eventManager->unregisterEventListener($listener);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertFalse(in_array($listener, $events[$event]));
        $this->assertTrue($result);

        $this->assertFalse($this->eventManager->unregisterEventListener(10, $event));
        $this->assertFalse($this->eventManager->unregisterEventListener(10, 'event2'));
    }

    public function testClearEventListenersForEvent() {
        $event = 'event';
        $callback = array($this, 'testClearEventListeners');

        $listener = $this->eventManager->registerEventListener($event, $callback);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertTrue(in_array($listener, $events[$event]));

        $this->eventManager->clearEventListeners($event);

        $events = $this->getProperty($this->eventManager, 'events');
        $this->assertFalse(isset($events[$event]));
    }

    public function testTriggerEventWithoutEvents() {
        $this->eventManager->triggerEvent('test');
    }

    public function testTriggerEventWithEvents() {
        $event = 'event';
        $this->executed = false;
        $callback = array($this, 'eventCallbackMethod');

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->triggerEvent($event);

        $this->assertTrue($this->executed, 'TestEvent has not been called');
    }

    public function testTriggerEventWithArguments() {
        $event = 'event';
        $this->executed = 0;
        $callback = array($this, 'eventCallbackMethodSum');

        $this->eventManager->registerEventListener($event, $callback);
        $this->eventManager->triggerEvent($event, array('value' => 1));
        $this->eventManager->triggerEvent($event, array('value' => 2));

        $this->assertEquals(3, $this->executed);
    }

    public function testTriggerEventWithWeights() {
        $event = 'event';
        $this->executed = 10;
        $callback1 = array($this, 'eventCallbackMethod');
        $callback2 = array($this, 'eventCallbackMethodSum');
        $callback3 = array($this, 'eventCallbackMethodMultiply');
        $callback4 = array($this, 'eventCallbackMethodSubstract');

        $this->eventManager->registerEventListener($event, $callback3);
        $this->eventManager->registerEventListener($event, $callback1, 20);
        $this->eventManager->registerEventListener($event, $callback4, 99);
        $this->eventManager->registerEventListener($event, $callback2, 10);
        $this->eventManager->triggerEvent($event, array('value' => 7));

        // 1: 10 + 7 = 17
        // 2: 7
        // 3: 7 * 7 = 49
        // 4: 49 - 7 = 42

        $this->assertEquals(42, $this->executed);
    }

    public function testTriggerEventWithPreventDefault() {
        $event = 'event';

        $this->executed = 10;

        $callback1 = array($this, 'eventCallbackMethod');
        $callback2 = array($this, 'eventCallbackMethodSum');
        $callback3 = array($this, 'eventCallbackMethodMultiply');
        $callback4 = array($this, 'eventCallbackMethodSubstract');
        $callback5 = array($this, 'eventCallbackMethodPreventDefault');

        $this->eventManager->registerEventListener($event, $callback3);
        $this->eventManager->registerEventListener($event, $callback1, 20);
        $this->eventManager->registerEventListener($event, $callback5, 70);
        $this->eventManager->registerEventListener($event, $callback4, 99);
        $this->eventManager->registerEventListener($event, $callback2, 10);
        $this->eventManager->triggerEvent($event, array('value' => 7));

        // 1: 10 + 7 = 17
        // 2: 7
        // 3: 7 * 7 = 49
        // 4: stop chain
        // 5: 49 - 7 = 42

        $this->assertEquals(49, $this->executed);
    }

    /**
     * @dataProvider providerEventWithInvalidEventThrowsException
	 * @expectedException pallo\library\event\exception\EventException
     */
    public function testRunEventWithInvalidEventThrowsException($event) {
        $this->eventManager->triggerEvent($event);
    }

    /**
     * @dataProvider providerEventWithInvalidEventThrowsException
	 * @expectedException pallo\library\event\exception\EventException
     */
    public function testClearEventsWithInvalidEventThrowsException($event) {
        $this->eventManager->clearEventListeners($event);
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