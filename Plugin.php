<?php
namespace App\Plugins\managementView;

use Exceedone\Exment\Services\Plugin\PluginViewBase;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Model\CustomColumn;

class Plugin extends PluginViewBase
{
    // (3) プラグイン独自の設定を追加する場合はコメントアウト
    // protected $useCustomOption = true;

    /**
     * (2) 一覧表示時のメソッド。"grid"固定
     */
    public function grid()
    {
        $values = $this->values();
        // (5) ビューを呼び出し
        return $this->pluginView('sample', ['values' => $values]);
    }

    /**
     * (2) このプラグイン独自のエンドポイント
     */
    public function update(){
        $value = request()->get('value');

        $custom_table = CustomTable::getEloquent(request()->get('table_name'));
        $custom_value = $custom_table->getValueModel(request()->get('id'));

        $custom_value->setValue($value)
            ->save();

        return response()->json($custom_value);
    }

    /**
     * (3) ビュー設定画面で表示するオプション
     * Set view option form for setting
     *
     * @param Form $form
     * @return void
     */
    public function setViewOptionForm($form)
    {
        //　独自設定を追加する場合
        $form->embeds('custom_options', '詳細設定', function($form){
            $form->select('category', 'カテゴリ列')
                ->options($this->custom_table->getFilteredTypeColumns([ColumnType::SELECT, ColumnType::SELECT_VALTEXT])->pluck('column_view_name', 'id'))
                ->required()
                ->help('カテゴリ列を選択してください。カンバンのボードに該当します。カスタム列種類「選択肢」「選択肢(値・見出し)」が候補に表示されます。');
        });

        //　フィルタ(絞り込み)の設定を行う場合
        static::setFilterFields($form, $this->custom_table);

        // 並べ替えの設定を行う場合
        static::setSortFields($form, $this->custom_table);
    }


    /**
     * (4) プラグインの編集画面で設定するオプション。全ビュー共通で設定する
     *
     * @param [type] $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        // 必要な場合、追加
        // $form->text('access_key', 'アクセスキー')
        //     ->help('YouTubeのアクセスキーを入力してください。');
    }


    // 以下、かんばんビューで必要な処理 ----------------------------------------------------

    protected function values(){
        $query = $this->custom_table->getValueQuery();

        // データのフィルタを実施
        $this->custom_view->filterModel($query);

        // データのソートを実施
        $this->custom_view->sortModel($query);

        // 値を取得
        $items = collect();
        $query->chunk(1000, function($values) use(&$items){
            $items = $items->merge($values);
        });

        $boards = $this->getBoardItems($items);

        return $boards;
    }


    protected function getBoardItems($items){
        $category = CustomColumn::getEloquent($this->custom_view->getCustomOption('category'));
        $options = $category->createSelectOptions();

        // set boards
        $boards_dragTo = collect($options)->map(function($option, $key){
            return "board-id-$key";
        })->toArray();

        $boards = collect($options)->map(function($option, $key) use($category, $boards_dragTo){
            return [
                'id' => "board-id-$key",
                'column_name' => $category->column_name,
                'key' => $key,
                'title' => $option,
                'drapTo' => $boards_dragTo,
                'item' => [],
            ];
        })->values()->toArray();

        foreach($items as $item){
            $c = array_get($item, 'value.' . $category->column_name);

            foreach($boards as &$board){
                if(!isMatchString($c, $board['key'])){
                    continue;
                }

                $board['item'][] = [
                    'id' => "item-id-$item->id",
                    'title' => $item->getLabel(),
                    'dataid' => $item->id,
                    'table_name' => $this->custom_table->table_name
                ];
            }
        }

        return $boards;
    }
}