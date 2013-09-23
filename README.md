# Pallo: System Library

Event library of the PHP Pallo framework.

## Code Sample

Check this code sample to see the possibilities of this library:

    <?php
    
    use pallo\library\event\EventManager;

    $eventManager = new EventManager();
    
    // add some event listeners
    $eventManager->registerEventListener('event', 'callback'); // provide a name of the event and a callback
    $eventManager->registerEventListener('event', 'callback', 10); // added a weight to influence order
    $eventManager->registerEventListener('test', 'onEvent');
    
    // trigger a event
    $eventManager->triggerEvent('test');
    $eventManager->triggerEvent('test', array('var' => 'value'));
    
    // event listener callback
    function onEvent(Event $event) {
        echo $event->getName();
        echo $event->getArgument('var');
        
        $event->setPreventDefault(); // stop the listener invokation after this one
    }