<?php

// (1)
namespace App\Plugins\ManagementView;

use Encore\Admin\Widgets\Box;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Services\Plugin\PluginPageBase;
use GuzzleHttp\Client;
use App\Models\ContractManagement;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;

class Plugin extends PluginPageBase
{
    protected $useCustomOption = true;



    /**
     * 初期表示
     * @return void
     */
    public function index()
    {
        return $this->getIndexBox();
    }
    /**
     * 契約期間変更時処理
     */
    private function changePeriod(){
        return $this->getIndexBox();
    }
    private function newContract(){
        $tableObj = CustomTable::getEloquent('contract_management');
        $form = $tableObj->custom_forms()->first();
        return $form;
    }

    /**
     * bladeを返す
     * @return void
     */
    protected function getIndexBox(){

        return view('exment_management_view::index', [
            'contents' => $this->getContents(),
            'changePeriod' => $this->getRouteUri('changePeriod'),
            'newContract' => $this->getRouteUri('newContract'),
        ]);
    }

    /**
     * bladeに渡す配列を取得
     */
    private function getContents(){
        $contents = array(['contract_id'=>'1','company'=>'スタイルフリー','name'=>'服部','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'2','company'=>'ヨコタ','name'=>'村瀬','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('work','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('nonel','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'3','company'=>'ITP','name'=>'西尾','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('work-surplus','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'4','company'=>'ピーネックス','name'=>'田中','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('deadline-over','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'5','company'=>'ウイングノア','name'=>'岡本','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('pre-complete','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'6','company'=>'アテナ','name'=>'金田','money'=>'○○','contract_kind'=>'派遣','contract_priod'=>'単月','tasks'=>array('個別契約書作成'=>array('complete','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'個別契約書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'7','company'=>'パレットリンク','name'=>'佐藤','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'8','company'=>'ヨコタ','name'=>'小林','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'9','company'=>'SJC','name'=>'水野','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'10','company'=>'パレットリンク','name'=>'中桐','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))],
                          ['contract_id'=>'11','company'=>'SJC','name'=>'清水','money'=>'○○','contract_kind'=>'準委任','contract_priod'=>'単月','tasks'=>array('見積受け取り'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書発注'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書押印'=>array('none','none','none','none','none','none','none','none','none','none','none','none'),'注文書送付'=>array('none','none','none','none','none','none','none','none','none','none','none','none'))]);

        return $contents;
    }
}