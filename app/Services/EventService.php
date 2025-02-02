<?php
namespace Amin\Event\Services;
use Amin\Event\Models\Event;
class EventService{
    private $event_model;
    public function __construct(){
        $this->event_model=new Event();
    }
    public function get($request,$user=null,$condi=[]){
        try{
            $events=$this->event_model
            ->where(function($query) use ($user){
                if($user && $user->role != 'admin')
                    $query->where('created_by', $user->id);
            })
            ->where(function($query) use ($condi){
                if(isset($condi['where']))
                    $query->where($condi['where']);
                if(isset($condi['orderBy']))
                    $query->orderBy($condi['orderBy']);
                else
                    $query->orderBy('id', 'desc');
            });
            if(isset($condi['paginate']))
                $events=$events->paginate($condi['paginate']);
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
    public function show($request,$condi=[]){
        try{
            $event=$this->event_model
                ->where(function($query) use ($condi){
                    if(isset($condi['withRelation']))
                        $query->with($condi['withRelation']);
                })
                ->where($condi['where'])
                ->where(function($query) use ($condi){
                    if(isset($condi['whereOr']))
                        $query->whereOr($condi['whereOr']);
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