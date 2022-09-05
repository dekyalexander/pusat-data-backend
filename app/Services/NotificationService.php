<?php
namespace App\Services;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class NotificationService {
    public $success = 200;
    public $unauth = 401;
    public $error = 500;
    public $conflict = 409;
  
    protected $notificationRepository;
  
    public function __construct(NotificationRepository $notificationRepository)
    {
      $this->notificationRepository = $notificationRepository;
    }

    public function getData($requestParams){
        $noidentitas = $requestParams['noindentitas'];
        $result = $this->notificationRepository->getData($noidentitas);
        return $result;
      }
  
      public function getTotalUnRead($requestParams){
        $noidentitas = $requestParams['noindentitas'];
        $result = $this->notificationRepository->getTotalUnRead($noidentitas);
        return $result;
      }
  
      public function update($requestParamId)
      {  
  
          $dataSimpan = [
              'readed_notification' => 1
          ];
                        
          $result = $this->notificationRepository->update($dataSimpan, $requestParamId);
              
          return $result; 
  
      }

}