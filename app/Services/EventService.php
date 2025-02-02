<?php
namespace Amin\Event\Services;
use Amin\Event\Models\Event;
class EventService{
    private $event_model;
    public function __construct(){
        $this->event_model=new Event();
    }
    public function get($request,$user=null,$whereCond=null,$withRelation=null,$orderBy=null,$paginate=0,$whereColumn=null){
        try{
            $events=$this->event_model
            ->where(function($query) use ($request){
                if($whereColumn)
                    $query->whereColumn($whereColumn);
            })
            ->where(function($query) use ($user){
                if($user && $user->role != 'admin')
                    $query->where('created_by', $user->id);
            })
            ->where(function($query) use ($whereCond,$orderBy){
                if($whereCond)
                    $query->where($whereCond);
                if($orderBy)
                    $query->orderBy($orderBy);
                else
                    $query->orderBy('id', 'desc');
            });
            if($paginate)
                $events=$events->paginate($paginate);
            else
                $events=$events->get();
            $res['status']=200;
            $res['data']=$events;
        }
        catch(\Exception $e){
            $res['status']=500;
            $res['errors'][]=$e->getMessage();
        }
        return $res;
    }
    public function show($request,$whereCond=null,$whereOrCond=null,$withRelation=null){
        try{
            $event=$this->event_model
                ->where(function($query) use ($withRelation){
                    if($withRelation)
                        $query->with($withRelation);
                })
                ->where($whereCond)
                ->where(function($query) use ($whereOrCond){
                    if($whereOrCond)
                        $query->whereOr($whereOrCond);
                })
                ->first();
            $res['status']=200;
            $res['data']=$event;
        }
        catch(\Exception $e){
            $res['status']=500;
            $res['errors'][]=$e->getMessage();
        }
        return $res;
    }
}