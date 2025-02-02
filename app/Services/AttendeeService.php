<?php
namespace Amin\Event\Services;
use Amin\Event\Models\Attendee;
use Amin\Event\Helpers\Utils;
use Amin\Event\Helpers\JsonResponse;
use Amin\Event\Models\Event;
use Amin\Event\Classes\DB;
class AttendeeService{
    private $attendee_model=null;private $event_model=null;
    public function __construct(){
        $this->attendee_model=new Attendee();
        $this->event_model=new Event();
    }
    public function get($request){
        $user=Utils::getUserSession();
        try{
            $attendees=$this->attendee_model
            ->with('event')
            ->with('hosted_by')
            ->where('event_id', $request->event_id)
            //->with('hosted_by')
            ->where(function($query) use ($user){
                if($user->role != 'admin')
                    $query->where('user_id', $user->id);
            })
            ->get();
            $res['status']=200;
            $res['data']=$attendees;
        }
        catch(\Exception $e){
            $res['status']=500;
            $res['errors'][]=$e->getMessage();
        }
        return $res;
    }
    public function store($request,$attendee){
        $event=$this->event_model->find($request->event_id);
        if(empty($event->id)){
            $res['status']=400;
            $res['errors'][]='Event not found';
            return $res;
            //JsonResponse::send($res['status'], $res);
        }
        $attendee['user_id']=$event->created_by;
        $is_exist=$this->attendee_model
            ->where('event_id', $request->event_id)
            ->where('phone', $request->phone)
            ->first();
        if(!empty($is_exist->id)){
            //JsonResponse::send(400, ['errors' => ['You have already registered for this event']]);
            $res['status']=400;
            $res['errors'][]='You have already registered for this event';
            return $res;
        }
        $current_capacity=$event->total_attendee + 1;
        if($event->capacity < $current_capacity){
            $res['status']=400;
            $res['errors'][]='Event is full';
            return $res;
            //JsonResponse::send($res['status'], $res);
        }
        DB::beginTransaction();
        try{
            $this->attendee_model->insert($attendee);
            $event->total_attendee=$current_capacity;
            $event->save();
            DB::commit();
            $res['status']=200;
            $res['message']='Attendee registered successfully';
            //JsonResponse::send($res['status'], $res);
        }
        catch(\Exception $e){
            DB::rollBack();
            $res['status']=500;
            $res['errors'][]=$e->getMessage();
        }
        return $res;
    }
}