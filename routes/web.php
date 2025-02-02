<?php
    use Amin\Event\Classes\Router;
    use Amin\Event\Controllers\AttendeeController;
    use Amin\Event\Controllers\HomeController;
    use Amin\Event\Controllers\LoginController;
    use Amin\Event\Controllers\EventController;
    use Amin\Event\Middleware\AuthMiddleware;
    $router=new Router();
    $router->get('/', [HomeController::class, 'index']);
    $router->post('/post/{id}/1/{section}', [HomeController::class, 'index']);
    $router->post('/api/v1/user/register', [LoginController::class, 'register']);
    $router->post('/api/v1/user/login', [LoginController::class, 'login']);
    $router->post('/api/v1/user/logout', [LoginController::class, 'logout'])->middleware(AuthMiddleware::class);

    $router->get('/api/v1/admin/events', [EventController::class, 'index'])->middleware(AuthMiddleware::class);
    $router->post('/api/v1/event/store', [EventController::class, 'store'])->middleware(AuthMiddleware::class);
    $router->post('/api/v1/event/update/{id}', [EventController::class, 'update'])->middleware(AuthMiddleware::class);
    $router->delete('/api/v1/event/delete/{id}', [EventController::class, 'delete'])->middleware(AuthMiddleware::class);
    $router->get('/api/v1/event/{id}', [EventController::class, 'edit'])->middleware(AuthMiddleware::class);
    
    $router->get('/api/v1/event-details/{slug}', [EventController::class, 'show']);
    $router->get('/api/v1/events', [EventController::class, 'publicIndex']);
    $router->post('/api/v1/event/attendee/register', [AttendeeController::class, 'attendeeRegister']);
    $router->get('/api/v1/event/attendees/download', [AttendeeController::class, 'downloadAttendees'])->middleware(AuthMiddleware::class);
    $router->get('/api/v1/event/attendees', [AttendeeController::class, 'attendees'])->middleware(AuthMiddleware::class);
    $router->get('/api/v1/admin/attendees/events', [AttendeeController::class, 'events'])->middleware(AuthMiddleware::class);

    $router->handleRequest();
?>