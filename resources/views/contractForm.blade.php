<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
</head>
<body>
    <div class="main-contents">
        <h1 class="form-title">新規登録</h1>
        <form action="{{admin_url($saveNewContract)}}" class="contract-form" method="post">
            <div class="form-group">
                <label>氏名</label>
                <input type="text" class="form-control" name="name">
            </div>
            <div class="form-group">
                <label>契約分類</label>
                <select name="contract_classification" id="" class="form-control">
                    <option value="1">個人</option>
                    <option value="2">派遣</option>
                    <option value="3">準委任</option>
                </select>
            </div>
            <div class="form-group">
                <label>契約期間</label>
                <select name="contract_period" id="" class="form-control">
                    <option value="2">二ヵ月</option>
                    <option value="1">単月</option>
                    <option value="3">三ヵ月</option>
                </select>
            </div>
            <div class="form-group">
                <label>所属会社</label>
                <input type="text" class="form-control" name="company">
            </div>
            <div class="form-group">
                <label>年度</label>
                <input type="text" class="form-control" name="year">
            </div>
            <div class="form-group">
                <label>原価</label>
                <input type="text" class="form-control" name="cost">
            </div>
            <div class="form-group">
                <label>勤怠備考</label>
                <input type="text" class="form-control" name="attendance_note">
            </div>
            <div class="form-group">
                <label>初回契約月</label>
                <select name="start_month" id="" class="form-control">
                    <option value="1">1月</option>
                    <option value="2">2月</option>
                    <option value="3">3月</option>
                    <option value="4">4月</option>
                    <option value="5">5月</option>
                    <option value="6">6月</option>
                    <option value="7">7月</option>
                    <option value="8">8月</option>
                    <option value="9">9月</option>
                    <option value="10">10月</option>
                    <option value="11">11月</option>
                    <option value="12">12月</option>
                </select>
            </div>
            {{ csrf_field() }}
            <button type="submit" class="btn btn-primary contract-form-btn">Submit</button>
        </form>
    </div>
</body>