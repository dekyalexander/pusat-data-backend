<?php
namespace App\Repositories;
use App\Models\Notification;

class NotificationRepository {

    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function store($dataNotif){
        $result = Notification::create($dataNotif);
        return $result;       
    }

    public function getData($noidentitas){
        return $this->notification->where('receiver_id',$noidentitas)
        ->where('readed_notification','=',0)    
        ->orderBy('id','DESC')->take(5)->get();
    }

    public function getTotalUnRead($noidentitas){
        $jlhDataUnRead = $this->notification
            ->where('receiver_id', '=', $noidentitas)
            ->where('readed_notification','=',0)
            ->get();
        return $jlhDataUnRead->count();
    }

    public function update($dataSimpan, $requestParamId){
        $result = Notification::WHEREIN('id',$requestParamId)->update($dataSimpan);
        return $result;  
    }

}