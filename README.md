# Pallo: System Library

Event library of the PHP Pallo framework.

## EventManager

The EventManager interface is the facade for the event system.
You can add and remove event listeners and trigger events from here.

The GenericEventManager has lazy load capabilities through the EventLoader interface.

## Code Sample

Check this code sample to see the possibilities of this library:

    <?php
    
    use pallo\library\event\loader\io\EventListenerIO;
    use pallo\library\event\loader\GenericEventLoader;
    use pallo\library\event\GenericEventManager;
    use pallo\library\event\Event;

    $eventManager = new GenericEventManager();
        
    // add some event listeners
    $eventManager->addEventListener('event', 'callback'); // provide a name of the event and a callback
    $eventManager->addEventListener('event', 'callback', 10); // added a weight to influence order
    $eventManager->addEventListener('test', 'onEvent');
    
    // trigger a event
    $eventManager->triggerEvent('test');
    $eventManager->triggerEvent('test', array('var' => 'value'));
    
    // event listener callback
    function onEvent(Event $event) {
        echo $event->getName();
        echo $event->getArgument('var');
        
        $event->setPreventDefault(); // stop the listener invokation after this listener
    }
    
    // you can lazy load the events through a EventLoader
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
    
    // all the events to be read at the first trigger, but only loaded when the
    // actual event is triggered
    $eventManager->setEventLoader($eventLoader);