@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>用户信息</legend>
                <div class="layui-field-box layui-clear"  >
                    <div class="layui-col-md6" style="margin-left: 10%;margin-top: 50px;">
                         <form class="layui-form" action="{{url('admin/member/update')}}"  method="post">
                             <div class="layui-form-item">
                                 <label class="layui-form-label">ID</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="id" class="layui-input" value="{{$member->id}}" readonly="readonly" >
                                 </div>
                             </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">昵称</label>
                                <div class="layui-input-block">
                                    <input type="text"   class="layui-input" value="{{$member->nickname}}" readonly="readonly" >
                                </div>
                            </div>
                             <div class="layui-form-item">
                                 <label class="layui-form-label">房卡</label>
                                 <div class="layui-input-block">
                                     <input type="text"   class="layui-input" value="{{$member->num}}" readonly="readonly" >
                                 </div>
                             </div>
                             <div class="layui-form-item">
                                 <label class="layui-form-label">手机号</label>
                                 <div class="layui-input-block">
                                     <input type="number" name="phone"  class="layui-input"  value="@if($member->phone){{$member->phone}}@endif" >
                                     <input type="hidden" name="old_phone"  class="layui-input" value="{{$member->phone}}" >
                                 </div>
                             </div>


                            <div class="layui-form-item">
                                <label class="layui-form-label">充值</label>
                                <div class="layui-input-block">
                                    <input type="number" name="num"  class="layui-input">
                                </div>
                            </div>

                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="formDemo">立即提交</button>
                                    <a href="{{url('admin/member/index')}}"><button  type="button" class="layui-btn layui-btn-primary">返回</button></a>
                                    @if($member->is_agency == 0 )
                                       <a href='{{url("admin/agent/$member->id/create")}}' style="margin-left: 200px;" title="设置该用户为代理" onclick="return confirm('设置该用户为代理前,请确认填写了正确的手机号！代理密码默认8个8！')"><button type="button" class="layui-btn layui-btn-normal">设为代理</button></a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </fieldset>
            <script>
            layui.use('laydate', function(){
                var laydate = layui.laydate;
                var laypage = layui.laypage
                //执行一个laydate实例
                //日期范围
                laydate.render({
                    elem: '#test6'
                    ,range: '--'
                });
            });
        </script>
@stop
