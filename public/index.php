<?php 
    require_once __DIR__ . '../../vendor/autoload.php';
    require_once __DIR__ . '../../app/Classes/Cors.php';
    function event_root() {
		return \Amin\Event\Classes\EventRoot::getInstance();
	}
    $GLOBALS['event_root'] = event_root();
?>