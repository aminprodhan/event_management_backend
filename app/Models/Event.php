<?php
namespace Amin\Event\Models;
use Amin\Event\Classes\DBQuery;
use Amin\Event\Models\User;
class Event extends DBQuery{
    protected $table='events';
    public function getDateAttribute($value) {
        return date("d-m-Y H:i:s", strtotime($value));
    }
    public function setDateAttribute($value) {
        return date("Y-m-d H:i:s", strtotime($value));
    }
    public function hosted_by() {
        return $this->belongsTo(User::class, 'created_by');
    }
}