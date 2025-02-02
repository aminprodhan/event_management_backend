<?php
namespace Amin\Event\Models;
use Amin\Event\Classes\DBQuery;
use Amin\Event\Models\User;
class PersonalAccessToken extends DBQuery{
    protected $table='personal_access_tokens';
    public function user(){
        return $this->belongsTo(User::class,"tokenable_id");
    }
}