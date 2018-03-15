@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>修改密码</legend>
                <div class="layui-field-box layui-clear"  >
                    <div class="layui-col-md6" style="margin-left: 10%;margin-top: 50px;">
                         <form class="layui-form" action="{{url('admin/set/admin_save')}}"  method="post">
                             <div class="layui-form-item">
                                 <label class="layui-form-label">旧密码</label>
                                 <div class="layui-input-block">
                                     <input type="password" name="old_password" class="layui-input"  >
                                 </div>
                             </div>
                             <div class="layui-form-item">
                                 <label class="layui-form-label">新密码</label>
                                 <div class="layui-input-block">
                                     <input type="password" name="password" class="layui-input">
                                 </div>
                             </div>
                             <div class="layui-form-item">
                                 <label class="layui-form-label">重复新密码</label>
                                 <div class="layui-input-block">
                                     <input type="password" name="password_confirmation" class="layui-input" >
                                 </div>
                             </div>
                             <br>
                             <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="formDemo">立即提交</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </fieldset>
@stop
