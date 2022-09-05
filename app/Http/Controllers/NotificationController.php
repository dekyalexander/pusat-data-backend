<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public $success = 200;
    public $unauth = 401;
    public $error = 500;
    public $conflict = 409;
  
    protected $notificationService;
  
    public function __construct(NotificationService $notificationService)
    {
      $this->notificationService = $notificationService;
    }

    public function getData(Request $request){
        $requestParams = $request->all();
        try {
          $result = $this->notificationService->getData($requestParams);    
          return response($result);
        } catch (\Exception $e) {
          return response(['error' => $e->getMessage(), 'message' => 'failed get data']);
        }
    }

    public function getTotalUnRead(Request $request){
        $requestParams = $request->all();
        try {
          $result = $this->notificationService->getTotalUnRead($requestParams);    
          return response(['total' => $result]);
        } catch (\Exception $e) {
          return response(['error' => $e->getMessage(), 'message' => 'failed get data']);
        }
    }

    public function update(Request $request){
      
      $status  = $this->success;
      $message = 'Update Data Success';
      $requestParamId = $request->params['idUserLogin'];
      try{
        $data = $this->notificationService->update($requestParamId);
      } catch(\Exception $e){
        return $result = [
          'status' => $this->error,
          'error'  => $e->getMessage(),
          'message' => 'Update data fail',
        ];
      }

      return $result = response()->json([
          'data' => $data, 'status' => $status, 'message' => $message
      ]); 
    }
    
}
