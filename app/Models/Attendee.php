<?php
namespace Amin\Event\Models;
use Amin\Event\Classes\DBQuery;
use Amin\Event\Models\User;
use Amin\Event\Models\Event;
class Attendee extends DBQuery{
    protected $table='attendees';
    public function event(){
        return $this->belongsTo(Event::class, 'event_id');
    }
    public function hosted_by(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getCreated_atAttribute($value) {
        return date("d-m-Y H:i:s", strtotime($value));
    }
}