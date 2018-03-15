@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>修改商品信息</legend>
                <div class="layui-field-box layui-clear" >
                    <div class="layui-col-md6" style="margin-left: 10%;margin-top: 50px;">
                         <form class="layui-form" action="{{url('admin/goods/edit')}}"  method="post">
                             <input type="hidden" name="id"  value="{{$data['id']}}" >
                             <div class="layui-form-item">
                                 <label class="layui-form-label">排序</label>
                                 <div class="layui-input-block">
                                     <input type="number" name="sort" class="layui-input" value="{{$data['sort']}}" >
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">钻石数</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="num" class="layui-input" value="{{$data['num']}}" >
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">价格</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="money" class="layui-input" value="{{$data['money']}}" >
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">是否显示</label>
                                 <div class="layui-input-block">
                                     <input type="radio" name="is_show" value="0" title="是" @if($data['is_show']=='0') checked @endif >
                                     <input type="radio" name="is_show" value="1" title="否" @if($data['is_show']=='1') checked @endif>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit="" lay-filter="formDemo">立即提交</button>
                                    <a href="{{url('admin/goods/index')}}"><button  type="button" class="layui-btn layui-btn-primary">返回</button></a>
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
