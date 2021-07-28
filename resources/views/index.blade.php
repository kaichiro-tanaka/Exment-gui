<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
</head>
<body>
  <div class="main-box">
    <div class="box-header with-header">
      <div class="pull-left">
        <h3>外注契約管理表</h3>
      </div>
      <div class="pull-right">
        <div class="botn-group pull-right">
          <a href="{{ admin_url($newContract)}}" class="btn btn-sm btn-success"><span class="hidden-xs">＋新規&nbsp;</span></a>
        </div>
      </div>
    </div>
    <table class="management-table">
      <tr>
        <th class="row1">所属</th>
        <th class="row2">氏名</th>
        <th class="row3">原価</th>
        <th class="row4">契約形態</th>
        <th class="row5">契約期間</th>
        <th class="row6">タスク</th>
        <th class="row-month">7月</th>                  
        <th class="row-month">8月</th>                  
        <th class="row-month">9月</th>                  
        <th class="row-month">10月</th>                  
        <th class="row-month">11月</th>                  
        <th class="row-month">12月</th>                  
        <th class="row-month">1月</th>                  
        <th class="row-month">2月</th>                  
        <th class="row-month">3月</th>                  
        <th class="row-month">4月</th>                  
        <th class="row-month">5月</th>                  
        <th class="row-month">6月</th>                  
      </tr>
      @foreach($contents as $content)
        <?php $cnt = count($content['tasks']); ?>
        @foreach($content['tasks'] as $key => $value)     
        <tr>
          @if ($key == array_key_first($content['tasks'])) 
          <td rowspan="{{ $cnt }}">{{ $content['company'] }}</td>                                
          <td rowspan="{{ $cnt }}">{{ $content['name'] }}</td>
          <td rowspan="{{ $cnt }}">{{ $content['money'] }}</td>
          <form action="{{ admin_url($changePeriod)}}">                           
            <td rowspan="{{ $cnt }}"><select class="period" name="" id="" 
            
            
            ><option value="" selected="selected">{{ $content['contract_priod'] }}</option><option value="176">単月</option><option value="177">二ヵ月</option><option value="194">三ヵ月</option></select></td>
          </form>     
          <td rowspan="{{ $cnt }}">{{ $content['contract_kind'] }}</td> 
          @endif
          <td>{{ $key }}</td>
          @for ($i =  0; $i < 12 ; $i++)
          <form action="">
            @if ($value[$i] === 'work' || $value[$i] === 'work-surplus' || $value[$i] === 'deadline-over')
            <td class="<?php print htmlspecialchars($value[$i]) ?>"><input type="checkbox" name="example" value="サンプル" onchange=”submit(this.form)”></td>
            @elseif ($value[$i] === 'pre-complete')
            <td class="<?php print htmlspecialchars($value[$i]) ?>"><input type="checkbox" name="example" value="サンプル" checked onchange=”submit(this.form)”></td>
            @elseif ($value[$i] === 'complete')
            <td class="<?php print htmlspecialchars($value[$i]) ?>"><input type="checkbox" checked disabled></td>
            @else 
            <td class="<?php print htmlspecialchars($value[$i]) ?>"></td> 
            @endif
          </form>
          @endfor
        </tr>
        @endforeach
      @endforeach
    </table>
  </div>
</body>