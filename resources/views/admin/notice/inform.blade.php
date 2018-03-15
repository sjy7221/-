@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>通知设置</legend>
                <div class="layui-field-box layui-clear" >
                    <div class="layui-col-md6" style="margin-left: 10%;margin-top: 50px;">
                         <form class="layui-form" action="{{url('admin/notice/save')}}"  method="post">
                             <input type="hidden" name="id" value="{{$data->id}}">
                             <div class="layui-form-item layui-form-text">
                                 <label class="layui-form-label">通知</label>
                                 <div class="layui-input-block">
                                     <textarea name="content"  class="layui-textarea" style="height:100px;">{{$data->content}}</textarea>
                                 </div>
                             </div>
                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="formDemo">立即提交</button>
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
