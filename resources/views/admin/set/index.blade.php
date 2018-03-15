@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>系统设置</legend>
                <div class="layui-field-box layui-clear" >
                    <div class="layui-col-md6" style="margin-left: 10%;margin-top: 50px;">
                         <form class="layui-form" action="{{url('admin/set/edit')}}"  method="post">
                             <div class="layui-form-item">
                                 <label class="layui-form-label">提现手续费</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param1" class="layui-input" value="{{$set['param1']}}" >
                                     <div class="layui-form-mid layui-word-aux">设置提现扣除的手续费</div>
                                 </div>
                             </div>
                             <div class="layui-form-item">
                                 <label class="layui-form-label">提现金额</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param2" class="layui-input" value="{{$set['param2']}}" >
                                     <div class="layui-form-mid layui-word-aux">设置最少提现金额</div>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">一级佣金</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param3" class="layui-input" value="{{$set['param3']}}" >
                                     <div class="layui-form-mid layui-word-aux">一级返佣比例,单位为百分比,填0为不分佣</div>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">二级佣金</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param4" class="layui-input" value="{{$set['param4']}}" >
                                     <div class="layui-form-mid layui-word-aux">二级返佣比例,单位为百分比,填0为不分佣</div>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">绑定送钻石</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param5" class="layui-input" value="{{$set['param5']}}" >
                                     <div class="layui-form-mid layui-word-aux">绑定手机号送钻石数</div>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">新用户注册</label>
                                 <div class="layui-input-block">
                                     <input type="text" name="param6" class="layui-input" value="{{$set['param6']}}" >
                                     <div class="layui-form-mid layui-word-aux">新用户注册送钻石数</div>
                                 </div>
                             </div>

                             <div class="layui-form-item">
                                 <label class="layui-form-label">代理充值</label>
                                 <div class="layui-input-block">
                                     <input type="radio" name="param7" value="0" title="关闭" @if($set['param7']=='0') checked @endif >
                                     <input type="radio" name="param7" value="1" title="开启" @if($set['param7']=='1') checked @endif>
                                 </div>
                                 <div class="layui-form-mid layui-word-aux">开启后,代理可以在代理后台给下级玩家充钻石</div>
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
