@extends('agent.layouts')
@section('content')
        <style>
            .content {
                width: 100%;
                padding: 0px 15px 0px 10px;
            }

            .form-control {
                display: inline-block;
                width: auto;
            }

            .page {
                text-align: center;
            }

            .form-group {
                padding: 0 30px;
            }

            .from-btn{
                text-align: center;
            }
            .from-btn .btn{
                width: 70%;
            }
        </style>
        <div class="link">
            <a href="{{ url('agent/index')}}">首页</a> / 个人信息 / 修改信息
        </div>

        <form class="form-inline cmxform form-horizontal adminex-form" method="post"
              action="{{url('agent/changepw_store')}}" novalidate="novalidate" id="frm">
              {{ csrf_field()}}
            <input type="hidden" name="id" value="{{$data->id}}">
            <input type="hidden" name="salts" value="{{$data->salts}}">
            <input type="hidden" name="old_password_confirmation" value="{{$data->password}}">
            <div class="form-group">
                <label for="exampleInputName2">提现方式：</label>
                <select name="ti_type" class="form-control">
                    <option value="1" @if($data->ti_type=='1') selected @endif>支付宝</option>
                    <option value="2" @if($data->ti_type=='2') selected @endif>银行卡</option>
                </select>
            </div>
            <div class="form-group">
                <label for="exampleInputName2">提现姓名：</label>
                <input type="text" class="form-control"  name="realname"  value="{{$data->realname}}" required="required">
            </div>
            <div class="form-group">
                <label for="exampleInputName2">提现账号：</label>
                <input type="text" class="form-control"  name="number" value="{{$data->number}}" required="required">
            </div>

            <div class="form-group">
                <label for="exampleInputName2">原 密 码 ：</label>
                <input type="password" class="form-control" id="old_password" name="old_password">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">新 密 码 ：</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="exampleInputEmail2">再次输入：</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
            </div>
            <div class="from-btn">
                <input type="submit" class="btn btn-primary" value="确认修改">
            </div>
        </form>
     <script>
         $("#frm").submit(function () {
             $sub = true;
             if($("input[name='bank']").val() ==""){
                 alert('提现方式不能为空');
                 $sub = false;
             }
             if($("input[name='cordname']").val() ==""){
                 alert('提现姓名不能为空');
                 $sub = false;
             }
             if($("input[name='cordnum']").val() ==""){
                 alert('提现账号不能为空');
                 $sub = false;
             }
               if($("#old_password").val() !== "" || $("#password").val() !== "" || $("#password_confirmation").val() !== ""){
                    if($("#old_password").val() == ""){
                        alert('请输入原密码');
                        $sub = false;
                    }
                    if($("#password").val() == "" || ($("#password").val() != $("#password_confirmation").val()) ){
                        alert('两次密码不一致');
                        $sub = false;
                    }
                   var str = $("#password").val();
                   var ret = /^[a-zA-Z0-9_]{5,20}$/;
                   if(!ret.test(str)){
                       alert('密码只能是6到20位字母或数字');
                       $sub = false;
                    }
               }
             return $sub;
         });
     </script>

@stop