<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <script type="text/javascript" src="http://localhost:8080/admin/plugins/contract_management_view/public/js/index.js"></script>
</head>
<body>
  <div class="main-box">
    @if($contents == null)
    <div class="none-contents">
      <h3 class="none-content">外注契約管理表</h3>
      <p class="none-content"class="none-h3">今年度の契約はまだ登録されていません。新規登録してください。</p>
      <div class="none-content">
        <a href="{{ admin_url($newContract) }}" class="btn btn-sm btn-success"><span class="hidden-xs">＋新規契約&nbsp;</span></a>
      </div>
    </div>
    @else
    <form action="{{ admin_url($chengeStatus) }}" method="POST">
      <div class="box-header with-header">
        <div class="pull-left">
          <h3>外注契約管理表</h3>
        </div>
        <div class="pull-right">
          <div class="botn-group pull-right">
            <div class="botn-group pull-right">
              <a href="{{ admin_url($newContract) }}" class="btn btn-sm btn-success"><span class="hidden-xs">＋新規契約&nbsp;</span></a>
            </div>
            <div class="botn-group pull-right">
              <button type="submit" class="btn btn-sm btn-primary" style="margin: 5px 0px 0px 0px;">保存</button>
            </div>
          </div>
        </div>
      </div>
      <table class="management-table">
        <tr>
          <th class="row2">管理Gr</th>
          <th class="row5">契約形態</th>
          <th class="row1">所属</th>
          <th class="row1">氏名</th>
          <th class="row3">契約金額</th>
          <th class="row4">契約期間</th>
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
        @php $mainCount = 0; @endphp
        @foreach($contents as $content)
          @php 
            $cnt = count($content['tasks']);
            $count = 1;
          @endphp
          @foreach($content['tasks'] as $key => $value)     
          <tr>
            @if ($key == array_key_first($content['tasks'])) 
            <td rowspan="{{ $cnt }}">{{ $content['group'] }}</td>    
            <td rowspan="{{ $cnt }}">{{ $content['contract_kind'] }}</td>                             
            <td rowspan="{{ $cnt }}">{{ $content['company'] }}</td>                                
            <td rowspan="{{ $cnt }}">{{ $content['name'] }}</td>
            <td rowspan="{{ $cnt }}">{{ $content['money'] }}</td>                          
            <td rowspan="{{ $cnt }}">{{ $content['contract_priod'] }}</td>
            @endif
            <td>{{ $key }}</td>
            @for ($i =  0; $i < 12 ; $i++)      
              @if ($value[$i] === 'work')
              <td class="{{ $value[$i] }}">  
                <input type="hidden" name="order{{ $mainCount }}" value="{{ $count }}">
                <input type="hidden" name="contract_id{{ $mainCount }}" value="{{ $content['contract_id'] }}">
                <input type="hidden" name="month{{ $mainCount }}" value="{{ $i }}">
                <input type="hidden" name="year{{ $mainCount }}" value="{{ $content['year'] }}">
                <input type="hidden" name="status{{ $mainCount }}" value="work">
                <input type="checkbox" name="status{{ $mainCount }}" value="pre-complete">
                {{ csrf_field() }}
                <!-- <input type="checkbox" name="status" value="pre-complete" id="box" onclick="onClickHandler()">
                <input type="submit"> -->
                <!-- <button type="submit" class="button1" style="padding: 0px 1px 0px 2px; height:22px; width: 21px; border: none; background-color: white;">
                  <i class="fa fa-square-o" style="font-size:1.2em; color:#1da1f2;"></i>
                </button> -->
              </td>
              @elseif ($value[$i] === 'pre-complete')
              <td class="{{ $value[$i] }}">
                <input type="hidden" name="order{{ $mainCount }}" value="{{ $count }}">
                <input type="hidden" name="contract_id{{ $mainCount }}" value="{{ $content['contract_id'] }}">
                <input type="hidden" name="month{{ $mainCount }}" value="{{ $i }}">
                <input type="hidden" name="year{{ $mainCount }}" value="{{ $content['year'] }}">
                <input type="hidden" name="status{{ $mainCount }}" value="work">
                <input type="checkbox" name="status{{ $mainCount }}" value="pre-complete" checked>
                {{ csrf_field() }}
                <!-- <input type="checkbox" name="status" value="work" checked id="box" onclick="onClickHandler()">
                <input type="submit"> -->
                <!-- <button type="submit" class="button1" style="padding: 0px 1px 0px 2px; height:22px; width: 21px; border: none; background-color: white;">
                  <i class="fa fa-check-square" style="font-size:1.2em; color:#1da1f2;"></i>
                </button> -->
              </td>
              @elseif ($value[$i] === 'deadline-over')
              <td class="{{ $value[$i] }}">
                <input type="hidden" name="order{{ $mainCount }}" value="{{ $count }}">
                <input type="hidden" name="contract_id{{ $mainCount }}" value="{{ $content['contract_id'] }}">
                <input type="hidden" name="month{{ $mainCount }}" value="{{ $i }}">
                <input type="hidden" name="year{{ $mainCount }}" value="{{ $content['year'] }}">
                <input type="hidden" name="status{{ $mainCount }}" value="deadline-over">
                <input type="checkbox" name="status{{ $mainCount }}" value="pre-complete">
                {{ csrf_field() }}
                <!-- <input type="checkbox" name="status" value="work" checked id="box" onclick="onClickHandler()">
                <input type="submit"> -->
                <!-- <button type="submit" class="button1" style="padding: 0px 1px 0px 2px; height:22px; width: 21px; border: none; background-color: #fc2600;">
                  <i class="fa fa-square" style="font-size:1.2em; color:white;"></i>
                </button> -->
              </td>
              @elseif ($value[$i] === 'complete')
              <td class="{{ $value[$i] }}"><input type="checkbox" checked disabled></td>
              @elseif ($value[$i] === 'passed')
              <td class="{{ $value[$i] }}"></td>
              @else 
              <td class="{{ $value[$i] }}"></td> 
              @endif
            @endfor
            @php 
              $count++; 
              $mainCount++;
            @endphp
          </tr>
          @endforeach
        @endforeach
        <input type="hidden" name="count" value="{{ $mainCount }}">
      </table>
      @endif
    </form>
  </div>
</body>
</html>
