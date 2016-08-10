# Ride: Event Library

Event library of the PHP Ride framework.

## EventManager

The _EventManager_ interface is the facade for this event system.
You can add and remove event listeners and trigger events from here.

A generic implementation is provider by the _GenericEventListener_ class.

## Event

When you trigger an event on the _EventManager_, an _Event_ instance is created and passed to the listeners.
The _Event_ class holds the name of the event, potential arguments and gives you the possibility to stop cascading to the following listeners.

## EventListener

An _EventListener_ instance defines the event, the callback and weight of a listener.
Listeners with a lower weight, will be invoked before listeners with a higher weight.

## EventLoader

The _GenericEventManager_ has lazy load capabilities through the _EventLoader_ interface.

This interface's purpose is to start resolving listeners when the event is triggered for the first time.

## Code Sample

Check this code sample to see the possibilities of this library:

```php
<?php

use ride\library\event\loader\io\EventListenerIO;
use ride\library\event\loader\GenericEventLoader;
use ride\library\event\GenericEventManager;
use ride\library\event\Event;

$eventManager = new GenericEventManager();
    
// add some event listeners
$eventManager->addEventListener('event', 'callback'); // provide a name of the event and a callback
$eventManager->addEventListener('event', array('MyClass', 'callback'), 10); // added a weight to influence order
$eventManager->addEventListener('test', 'onEvent');

// trigger an event
$eventManager->triggerEvent('test');
$eventManager->triggerEvent('test', array('var' => 'value'));

// event listener callback
function onEvent(Event $event) {
    echo $event->getName();
    echo $event->getArgument('var');
    
    $event->setPreventDefault(); // stop the listener cascade after this listener
}

// you can lazy load the events through an EventLoader
class YourEventListenerIO implements EventListenerIO {

    public function readEventListeners() {
        return array(
            'event' => array(
                new EventListener('event', 'callback'),
            );
        );
    }

}

$eventListenerIO = new YourEventListenerIO();
$eventLoader = new GenericEventLoader($eventListenerIO);

// all the events will be read at the first trigger, but only initialized when 
// the actual event is triggered
$eventManager->setEventLoader($eventLoader);
```
