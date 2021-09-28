<?php

// (1)
namespace App\Plugins\ContractManagementView;

use Encore\Admin\Widgets\Box;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\Services\Plugin\PluginPageBase;
use GuzzleHttp\Client;
use App\Models\ContractManagement;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\Log;
use DateTime;

use function Psy\debug;

class Plugin extends PluginPageBase
{
    protected $useCustomOption = true;



    /**
     * 初期表示
     * @return void
     */
    public function index(){
        $taskTable = CustomTable::getEloquent('task')->getValueModel();
        $deadlineModel = CustomColumn::getEloquent('deadline', $taskTable);
        $statusModel = CustomColumn::getEloquent('status', $taskTable);
        $isObject = $taskTable->query()
                              ->whereDate($deadlineModel->getIndexColumnName(), '<', date('y-m-d'))
                              ->where($statusModel->getIndexColumnName(), 'work')
                              ->count();

        if ($isObject > 0) {
            $targetTasks = $taskTable->whereDate($deadlineModel->getIndexColumnName(), '<', date('y-m-d'))
                                     ->where($statusModel->getIndexColumnName(), 'work')
                                     ->get();
            foreach($targetTasks as $targetTask){
                $id = $targetTask->id;
                $value = CustomTable::getEloquent('task')->getValueModel($id);
                $value->setValue('status', 'deadline-over');
                $value->save();
            }
         
            
        }
        return $this->getIndexBox();
    }
    /**
     * 契約期間変更時処理
     */
    public function changePeriod(){
        return $this->getIndexBox();
    }
    /**
     * 新規ボタン押下時処理
     */
    public function newContract(){
        return view('exment_contract_management_view::contractForm',[
            'saveNewContract' => $this->getRouteUri('saveNewContract'),
        ]);
    }
    /**
     * 新規登録時処理
     */
    public function saveNewContract(){
        
        //リクエスト取得
        $request = request();
        //モデル取得
        $model = CustomTable::getEloquent('contract_management')->getValueModel();
        
        $taskMst = CustomTable::getEloquent('task_mst')->getValueModel();
        $contractTypeModel = CustomColumn::getEloquent('contract_type', $taskMst);
        $orderModel = CustomColumn::getEloquent('order', $taskMst);
        //新規契約登録
        $model->setValue('group', $request->get('group'));
        $model->setValue('name', $request->get('name'));
        $model->setValue('contract_classification', $request->get('contract_classification'));
        $model->setValue('contract_period', $request->get('contract_period'));
        $model->setValue('company', $request->get('company'));
        $model->setValue('year', $request->get('year'));
        $model->setValue('cost', $request->get('cost'));
        $model->setValue('attendance_note', $request->get('attendance_note'));
        $model->setValue('start_month', $request->get('start_month'));
        $model->save();
        //タスクマスタから該当タスク取得
        $contractType = (int)$request->get('contract_classification');
        $taskMstdatas = $taskMst->where($contractTypeModel->getIndexColumnName(), $contractType)
                                ->orderby($orderModel->getIndexColumnName(), 'asc')
                                ->get();
        //タスク生成
        $contractId = $model->max('id');
        $month = $request->get('start_month');
        $deadline = $this->getDeadline($month);
        $status = 'work';

        foreach($taskMstdatas as $taskMstdata){
            $task = CustomTable::getEloquent('task')->getValueModel();
            $remindTiming = $taskMstdata->getValue('remind_timing');
            $remindDate = $this->getRemindDate($remindTiming, $deadline);
            $taskName = $taskMstdata->getValue('task_name');
            $order = $taskMstdata->getValue('order');


            $task->setValue('contract_id', $contractId);
            $task->setValue('month', $month);
            $task->setValue('deadline', $deadline);
            $task->setValue('remind_date', $remindDate);
            $task->setValue('task_name', $taskName);
            $task->setValue('status', $status);
            $task->setValue('order', $order);
            $task->setValue('year', $request->get('year'));
            $task->save();
        }
        //push通知
        admin_toastr(trans('admin.save_succeeded'));
        //ページ表示
        return $this->index();

        
    }
    /**
     * ステータス変更時処理
     */
    public function chengeStatus(){
        //リクエスト取得
        $request = request();
        log::debug($request);
        $count = $request->get('count');
        for($i=0; $i<$count; $i++){
            $status = $request->get('status'.$i);
            $month = $this->getMonth((int)$request->get('month'.$i));
            $order = $request->get('order'.$i);
            $contractId = $request->get('contract_id'.$i);
            $year = $request->get('year'.$i);

            $taskModel = CustomTable::getEloquent('task')->getValueModel();
            $taskMst = CustomTable::getEloquent('task_mst')->getValueModel();
            $orderColumnModel = CustomColumn::getEloquent('order', $taskModel);
            $contractIdColumnModel = CustomColumn::getEloquent('contract_id', $taskModel);
            $yearColumnModel = CustomColumn::getEloquent('year', $taskModel);
            $statusColumnModel = CustomColumn::getEloquent('status', $taskModel);
            $monthColumnModel = CustomColumn::getEloquent('month', $taskModel);
            $taskNameColumnModel = CustomColumn::getEloquent('task_name', $taskMst);

            $taskdatas = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                ->where($orderColumnModel->getIndexColumnName(), $order)
                                ->where($yearColumnModel->getIndexColumnName(), $year)
                                ->where($monthColumnModel->getIndexColumnName(), $month)
                                ->get();

            foreach($taskdatas as $taskdata){
                $id = $taskdata->id;
                $value = CustomTable::getEloquent('task')->getValueModel($id);
                $value->setValue('status', $status);
                if($status == 'work'){
                    $value->setValue('complete_date', null);
                } else{
                    $value->setValue('complete_date', date('y-m-d'));
                }
                $value->save();
            }          
                
            $countTask = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                ->where($monthColumnModel->getIndexColumnName(), $month)
                                ->where($yearColumnModel->getIndexColumnName(), $year)
                                ->count();
            
            $countComplete = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                    ->where($monthColumnModel->getIndexColumnName(), $month)
                                    ->where($yearColumnModel->getIndexColumnName(), $year)
                                    ->where($statusColumnModel->getIndexColumnName(), 'pre-complete')
                                    ->count();
            
            if($countTask != 0 && $countTask == $countComplete){
                $CompleteTasks = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                ->where($monthColumnModel->getIndexColumnName(), $month)
                                ->where($yearColumnModel->getIndexColumnName(), $year)
                                ->where($statusColumnModel->getIndexColumnName(), 'pre-complete')
                                ->get();
                $model = CustomTable::getEloquent('contract_management')->getValueModel($contractId);
                $nextMonth = $model->getValue('contract_period');
                $month = (int)$month + $nextMonth;
                $deadline = $this->getDeadline($month);
                $status = 'work';
                if($month > 12){
                    $month = $month - 11;
                }
                foreach($CompleteTasks as $CompleteTask) {
                    $id = $CompleteTask->id;
                    $value = CustomTable::getEloquent('task')->getValueModel($id);
                    $value->setValue('status', 'complete');
                    $value->save();
                    log::debug($month);
                    if($month != 7){
                        $task = CustomTable::getEloquent('task')->getValueModel();
                        $taskMst = CustomTable::getEloquent('task_mst')->getValueModel();
                        $taskName = $CompleteTask->getValue('task_name');
                        $taskMstdatas = $taskMst->where($taskNameColumnModel->getIndexColumnName(), $taskName)
                                        ->get();
                        foreach($taskMstdatas as $taskMstdata){
                            $remindTiming = $taskMstdata->getValue('remind_timing');
                            $order = $taskMstdata->getValue('order');
                        }
                        $remindTiming = $taskMstdata->getValue('remind_timing');
                        $remindDate = $this->getRemindDate($remindTiming, $deadline);
            
                        $task->setValue('contract_id', $contractId);
                        $task->setValue('month', $month);
                        $task->setValue('deadline', $deadline);
                        $task->setValue('remind_date', $remindDate);
                        $task->setValue('task_name', $taskName);
                        $task->setValue('status', $status);
                        $task->setValue('order', $order);
                        $task->setValue('year', $request->get('year'.$i));
                        $task->save();
                    }
                }
                $model->setValue('contract_period', 1)
                    ->save();
            }
        }
        //push通知
        admin_toastr(trans('admin.save_succeeded'));
        //ページ表示
        return $this->index();
    }
    /**
     * タスク期日取得（月末営業日）
     */
    private function getDeadline($month){
        $deadlineMonth = (int)$month - 1;
        if($deadlineMonth < 1){
            $deadlineMonth = 12;
        }
        $year = date('Y');
        if($deadlineMonth < 6){
            $year = (int)$year +1;
        }
        //$holidays = getHolidays($year); 未実装
        $deadline = date('Y-m-d', strtotime('last day of ' . $year . '-' . $deadlineMonth));
        $deadline = $this->judgeday($deadline);
        return $deadline;
    }
    /**
     * 通知日取得
     */
    private function getremindDate($remindTiming,$deadline){
        $days = 0;
        $date = new DateTime($deadline);
        while (true){
            $date->modify('-1 days');
            $w = (int)$date->format('w');
            if($w > 0 && $w < 6){
                $days++;
                if($days == $remindTiming){
                    break;
                }
            }
        }
        $remindDate = $date->format('Y/m/d');
        return $remindDate;
    }
    /**
     * 祝日取得
     * 未実装
     * 
     */
    // TODO: 祝日取得
    private function getHolidays($year) {
      
    }
    /**
     * 営業日判定
     */
    private function judgeday($deadline){
        $date = new DateTime($deadline);
        while (true) {
            $w = (int)$date->format('w');
            //土日以外
            if ($w > 0 && $w < 6) {
                //祝日でない
                //TODO: 祝日取得関数実装後、実装
                if (true) {
                    $deadline = $date->format('Y/m/d');
                    break;
                }
            }
            $date->modify('-1 days');
        }
        return $deadline;
    }
    
        

    /**
     * bladeを返す
     * @return void
     */
    protected function getIndexBox(){
        $contents = $this->getContents();

        return view('exment_contract_management_view::index', [
            'contents' => $contents,
            'changePeriod' => $this->getRouteUri('changePeriod'),
            'newContract' => $this->getRouteUri('newContract'),
            'chengeStatus' => $this->getRouteUri('chengeStatus'),
        ]);
    }

    /**
     * bladeに渡す配列を取得
     */
    private function getContents(){
        $model = CustomTable::getEloquent('contract_management')->getValueModel();
        $yearColumn = CustomColumn::getEloquent('year', $model);
        $companyColumn = CustomColumn::getEloquent('company', $model);
        $groupColumn = CustomColumn::getEloquent('group', $model);
        $classColumn = CustomColumn::getEloquent('company', $model);
        $year = $this->getYear();
        $isData = $model->where($yearColumn->getIndexColumnName(), $year)
                        ->orderby($companyColumn->getIndexColumnName(), 'asc')
                        ->count();
        if($isData == 0){
            return $contents = null;
        }
        $contractDatas = $model->where($yearColumn->getIndexColumnName(), $year)
                               ->orderby($companyColumn->getIndexColumnName(), 'asc')
                               ->orderby($classColumn->getIndexColumnName(), 'asc')
                               ->orderby($groupColumn->getIndexColumnName(), 'asc')
                               ->get();
        


        foreach($contractDatas as $contractManageMentData){
            $group = $contractManageMentData->getValue('group');
            $contractId = $contractManageMentData->getValue('contract_id');
            $company = $contractManageMentData->getValue('company');
            $name = $contractManageMentData->getValue('name');
            $money = $contractManageMentData->getValue('cost');
            $contractKind = $contractManageMentData->getValue('contract_classification',true);
            $contractPriod = $contractManageMentData->getValue('contract_period',true);

            $taskModel = CustomTable::getEloquent('task')->getValueModel();
            $contractIdColumnModel = CustomColumn::getEloquent('contract_id', $taskModel);
            $orderColumnModel = CustomColumn::getEloquent('order', $taskModel);
            $yearColumnModel = CustomColumn::getEloquent('year', $taskModel);

            unset($taskDatas);
            unset($targetMonth);
            unset($initStatus);
            

            $taskDatas = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                   ->where($yearColumnModel->getIndexColumnName(), $year)
                                   ->orderby($orderColumnModel->getIndexColumnName(), 'asc')
                                   ->get();
            $targetMonth = $taskModel->where($contractIdColumnModel->getIndexColumnName(), $contractId)
                                     ->where($yearColumnModel->getIndexColumnName(), $year)
                                     ->max('id');
            $targetMonth = $taskModel->where($yearColumnModel->getIndexColumnName(), $year)
                                     ->find($targetMonth)
                                     ->getValue('month');
            $initStatus = $this->getInitStatus($targetMonth);
            
            foreach($taskDatas as $taskData){
                $month = $taskData->getValue('month');
                $taskName = $taskData->getValue('task_name');
                $status = $taskData->getValue('status');
                $tasks[$taskName][$month] = $status;
            }
            foreach($tasks as $taskName=> $month){
                $modifyTasks[$taskName] = $initStatus;
                foreach($month as $key=>$status){
                    $index = $this->getIndex($key);
                    $modifyTasks[$taskName][$index] = $status;
                }
            }
            
            $contents[] = array('contract_id'=>$contractId,'year'=>$year,'company'=>$company,'name'=>$name,'money'=>$money,'contract_kind'=>$contractKind,'contract_priod'=>$contractPriod,'tasks'=>$modifyTasks,'group'=>$group);
            unset($modifyTasks);
            unset($tasks);

        }

       
        // $contents = array(['contract_id'=>'1','company'=>'スタイルフリー','name'=>'服部','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'2','company'=>'ヨコタ','name'=>'村瀬','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('work','work','work','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('nonel','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'3','company'=>'ITP','name'=>'西尾','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('work-surplus','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'4','company'=>'ピーネックス','name'=>'田中','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('deadline-over','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'5','company'=>'ウイングノア','name'=>'岡本','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('pre-complete','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'6','company'=>'アテナ','name'=>'金田','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('complete','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'7','company'=>'パレットリンク','name'=>'佐藤','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'8','company'=>'ヨコタ','name'=>'小林','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'9','company'=>'SJC','name'=>'水野','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'10','company'=>'パレットリンク','name'=>'中桐','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
        //                   ['contract_id'=>'11','company'=>'SJC','name'=>'清水','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))]);

        return $contents;
    }
    /**
     * 年度取得
     */
    private function getYear(){
        $year = date('Y');
        $month = date('n');
        if((int)$month < 7){
            $year -= 1;
        }
        return $year;
    }

    private function getIndex($int){
        if((int)$int > 6){
            $index = (int)$int - 7;
        }else{
            $index = (int)$int + 5;
        }
        return $index;
    }
    private function getMonth($int){
        if((int)$int < 6){
            $index = (int)$int + 7;
        }else{
            $index = (int)$int - 5;
        }
        return $index;
    }
    private function getInitStatus($targetMonth){
        $index = $this->getIndex($targetMonth);
        for($i = 0; $i < 12; $i++){
            if($i < $index){
                $initStatus[] = 'passed';
            }else{
                $initStatus[] = 'none';
            }
        }
        return $initStatus;
    }
    
}