<?php
namespace Amin\Event\Controllers;
use Amin\Event\Classes\Request;
use Amin\Event\Helpers\JsonResponse;
use Amin\Event\Models\Event;
use Amin\Event\Classes\DB;
use Amin\Event\Helpers\Utils;
use Amin\Event\Models\Attendee;
use Amin\Event\Services\EventService;
class EventController{
    private $event;private $attendee;private $event_service;
    public function __construct(){
        $this->event=new Event();
        $this->attendee=new Attendee();
        $this->event_service=new EventService();
    }
    public function index(Request $request){
        $res=$this->event_service->get($request,Utils::getUserSession());
        $res['url']=$request->current_url; 
        JsonResponse::send($res['status'], $res);
    }
    public function store(Request $request){
        $request->validate([
            'title' => 'required',
            'capacity' => 'required|number',
            'date' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try{
            $this->event->insert([
                'title' => $request->title,
                'capacity' => $request->capacity,
                'type' => $request->type,
                'slug' => Utils::generateSlug($request->title),
                'created_by' => Utils::getUserSession()->id,
                'date' => date('Y-m-d H:i:s',strtotime($request->date)),
                'is_publish' => $request->is_publish ? 1 : 2,
                'description' => $request->description
            ]);
            DB::commit();
            $res['status']=200;
            $res['message']='Event created successfully';
        }
        catch(\Exception $e){
            DB::rollBack();
            $res['status']=500;
            $res['errors'][]=$e->getMessage();
        }
        JsonResponse::send($res['status'], $res);
    }
    public function update(Request $request,$event_id){
        $request->validate([
            'title' => 'required',
            'capacity' => 'required|number',
            'date' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try{
            $this->event->where('id', $event_id)->update([
                'title' => $request->title,
                'type' => $request->type,
                'slug' => Utils::generateSlug($request->title),
                'created_by' => Utils::getUserSession()->id,
                'capacity' => $request->capacity,
                'date' => date('Y-m-d H:i:s',strtotime($request->date)),
                'is_publish' => $request->is_publish ? 1 : 2,
                'description' => $request->description
            ]);
            DB::commit();
            $res['status']=200;
            $res['message']='Event updated successfully';
        }
        catch(\Exception $e){
            DB::rollBack();
            $res['status']=500;
            $res['message']=$e->getMessage();
        }
        JsonResponse::send($res['status'], $res);   
    }
    public function edit(Request $request,$event_id){
        $conds=[
            'where' => ['id' => $event_id]
        ];
        $res=$this->event_service->show($request,$conds);
        JsonResponse::send($res['status'], $res);
    }
    public function show(Request $request,$slug){
        $conds=[
            'where' => ['slug' => $slug],
            'whereOr' => ['id' => $slug],
            'withRelation' => ['hosted_by']
        ];

        $res=$this->event_service->show($request,$conds);
        if($res['status'] != 200 || !isset($res['data'])){    
            JsonResponse::send($res['status'], $res);
        }
        $event=$res['data'];
        unset($event->hosted_by->id);
        unset($event->hosted_by->password);
        $event->is_registration_open=1;
        if(!empty($event->id)){
            $left=$event->capacity - $event->total_attendee; 
            if($left <= 0){
                $event->is_registration_open=1;
            }
        }
        $res['status']=200;
        $res['data']=$event;
        JsonResponse::send($res['status'], $res);
        
    }
    public function delete(Request $request,$event_id){
        DB::beginTransaction();
        try{
            $this->event->where('id', $event_id)->delete();
            DB::commit();
            $res['status']=200;
            $res['message']='Event deleted successfully';
            JsonResponse::send($res['status'], $res);
        }
        catch(\Exception $e){
            DB::rollBack();
            $res['status']=500;
            $res['message']=$e->getMessage();
            JsonResponse::send($res['status'], $res);
        }
    }
    public function publicIndex(Request $request){
        
        $filter_day=$request->day ?? 0;$filter_time=$request->time ?? 0;$filter_sort=$request->sort ?? 0;
        $filter_serach=$request->search ?? '';
        $filter_where=[];$filter_orderBy=['id' => 'desc'];
        //today=0,tommorrow=1
        if($filter_day == 1){
            $filter_where[]=['date', '>=', date('Y-m-d 00:00:00')];
            $filter_where[]=['date', '<=', date('Y-m-d 23:59:59')];
        }
        else if($filter_day == 2){
            $filter_where[]=['date', '>=', date('Y-m-d 00:00:00', strtotime('+1 day'))];
            $filter_where[]=['date', '<=', date('Y-m-d 23:59:59', strtotime('+1 day'))];
        }
        else
            $filter_where[]=['date', '>=', date('Y-m-d 00:00:00')];

        if($filter_sort == 1)
            $filter_orderBy=['date' => 'asc'];

        if(!empty($filter_serach))
            $filter_where[]=['title', 'like', '%'.$filter_serach.'%'];
        if(!empty($filter_time)){
            $filter_where[]=['type', '=', $filter_time];
        }

        $filter_where[]=['capacity', '>', 'total_attendee',true];
        $filter_where[]=['is_publish', '=', 1];
        $conds=[
            'where' => $filter_where,
            'orderBy' => $filter_orderBy,
            'paginate' => 5,
        ];
        $res=$this->event_service->get($request,null,$conds);
        JsonResponse::send($res['status'], $res);
    }
    
}