<?php
namespace Amin\Event\Controllers;
use Amin\Event\Classes\Request;
use Amin\Event\Helpers\JsonResponse;
use Amin\Event\Models\Event;
use Amin\Event\Models\Attendee;
use Amin\Event\Classes\DB;
use Amin\Event\Helpers\Utils;
use Amin\Event\Services\AttendeeService;
use Amin\Event\Services\EventService;
class AttendeeController{
    private $event_service;private $attendee_service;
    public function __construct(){
        $this->event_service=new EventService();
        $this->attendee_service=new AttendeeService();
    }
    public function attendeeRegister(Request $request){
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'event_id' => 'required',
        ]);
        $info=[
            'event_id' => $request->event_id,
            'name' => $request->name,
            'phone' => $request->phone,
            //'user_id' => $event->created_by,
            'main_reason' => $request->main_reason,
        ];
        $res=$this->attendee_service->store($request,$info);
        JsonResponse::send($res['status'], $res);
    }
    public function attendees(Request $request){
        $res=$this->attendee_service->get($request);
        JsonResponse::send($res['status'], $res);
    }
    public function downloadAttendees(Request $request){
        //$logged_user=Utils::getUserSession();
        // if($logged_user->role != 'admin'){
        //     $res['status']=400;
        //     $res['errors'][]='You are not authorized to perform this action';
        //     JsonResponse::send($res['status'], $res);
        // }
        $res=$this->attendee_service->get($request);
        if($res['status'] != 200){
            JsonResponse::send($res['status'], $res);
        }
        $attendees=$res['data'];
        foreach($attendees as $attendee){
            $attendee->event_name=$attendee->event->title;
            $attendee->event_date=$attendee->event->date;
            $attendee->hosted_by_name=$attendee->hosted_by->name;
            unset($attendee->event);
            unset($attendee->hosted_by);
            unset($attendee->event_id);
            unset($attendee->created_by);
            unset($attendee->user_id);
            unset($attendee->id);
            unset($attendee->updated_at);
            unset($attendee->deleted_at);
        }
        Utils::array2csv($attendees);
    }
    public function events(Request $request){
        $user=Utils::getUserSession();
        $conds=[
            'where' => ['is_publish' => 1]
        ];
        $res=$this->event_service->get($request,$user,$conds);
        JsonResponse::send($res['status'], $res);
    }    
}