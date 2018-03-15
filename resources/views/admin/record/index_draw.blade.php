@extends('admin.layouts')
@section('content')
    <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
        <legend>提现管理</legend>
        <div class="layui-field-box">
            <div class="layui-col-xs12">
                <blockquote class="layui-elem-quote layui-quote-nm">
                    <form class="layui-form" action="{{ url('admin/record/index_draw')  }}">
                        <div class="layui-inline">
                            <label class="layui-form-label">提现单号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="sn"  class="layui-input" value="{{val('sn')}}" placeholder="提现单号">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">代理ID</label>
                            <div class="layui-input-inline">
                                <input type="number" name="id"  class="layui-input" value="{{val('id')}}" placeholder="代理ID">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现状态</label>
                            <div class="layui-input-inline">
                                <select name="is_pay">
                                    <option value=""></option>
                                    <option value="1" @if(val('is_pay')=='1') selected @endif>申请中</option>
                                    <option value="2" @if(val('is_pay')=='2') selected @endif>提现成功</option>
                                    <option value="3" @if(val('is_pay')=='3') selected @endif>提现驳回</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">提现方式</label>
                            <div class="layui-input-inline">
                                <select name="ti_type">
                                    <option value=""></option>
                                    <option value="1" @if(val('ti_type')=='1') selected @endif>支付宝</option>
                                    <option value="2" @if(val('ti_type')=='2') selected @endif>银行卡</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">申请时间</label>
                            <div class="layui-input-inline">
                                <input type="text" name='creat_time' value="{{val('creat_time')}}" class="layui-input" id="sheng" placeholder="申请时间">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">处理时间</label>
                            <div class="layui-input-inline">
                                <input type="text" name='finish_time' value="{{val('finish_time')}}"  class="layui-input" id="chu" placeholder="处理时间">
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <button class="layui-btn layui-btn-normal">搜索</button>
                            <button class="layui-btn layui-btn-warm" value="1" name="excel">导出Excel</button>
                        </div>

                    </form>
                </blockquote>
            </div>
            <div style="font-size: 17px;">总数：{{$num->num}}  &nbsp;&nbsp;&nbsp; 总提现金额数：{{$num->nums}} &nbsp;&nbsp;&nbsp;  总手续费：{{$num->rate}} &nbsp;&nbsp;&nbsp; 总实际金额数：{{$num->money}}</div>
            <table class="layui-table">
                <colgroup>
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="100">
                    <col width="150">
                    <col width="100">
                    <col width="100">
                    <col width="200">
                    <col width="100">
                    <col width="200">
                    <col width="200">
                    <col width="150">
                </colgroup>
                <thead>
                <tr>
                    <th>提现单号</th>
                    <th>代理ID</th>
                    <th>提现金额</th>
                    <th>手续费</th>
                    <th>实际到账金额</th>
                    <th>提现姓名</th>
                    <th>提现方式</th>
                    <th>提现账户</th>
                    <th>提现状态</th>
                    <th>申请时间</th>
                    <th>处理时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($data as $v)
                    <tr>
                        <td >{{$v->sn}}</td>
                        <td ><a href="{{url("admin/agent/index?id={$v->mid}")}}">{{$v->mid}}</a></td>
                        <td >{{$v->num}}</td>
                        <td >{{$v->rate}}</td>
                        <td >{{$v->money}}</td>
                        <td >{{$v->realname}}</td>
                        <td >
                            @if($v->ti_type==1)
                                <b style="color: #4b9aff">支付宝</b>
                            @elseif($v->ti_type==2)
                                <b style="color: #ff5716">银行卡</b>
                            @endif
                        </td>
                        <td >{{$v->number}}</td>
                        <td >
                            @if($v->is_pay==1)
                                <b style="color: #59ff0b">申请中</b>
                            @elseif($v->is_pay==2)
                                <b style="color: #104bff">提现完成</b>
                            @else
                                <b style="color: #ff3a4a">提现失败</b>
                            @endif
                        </td>
                        <td >{{date('Y-m-d H:i:s',$v->creat_time)}}</td>
                        <td >
                            @if($v->is_pay==1)
                                尚未处理
                            @else
                                {{date('Y-m-d H:i:s',$v->finish_time)}}
                            @endif
                        </td>

                            <td >
                                @if($v->is_pay==1)
                                    <a href='{{url("admin/record/{$v->sn}/2/draw_true")}}' onclick="return confirm('确定已经打款？')"><button class="layui-btn layui-btn-radius layui-btn-small layui-btn-primary">成功</button></a>
                                    <a href='javascrit:;' id="bb" sn="{{$v->sn}}"><button class="layui-btn layui-btn-radius layui-btn-small layui-btn-primary">驳回</button></a>
                                @elseif($v->is_pay==2)
                                    已处理
                                 @elseif($v->is_pay==3)
                                    <a href='javascrit:;' id="bb2" describe="{{$v->describe}}">驳回原因</a>
                                 @endif
                            </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="12" class="page">{{$data->appends($input)->links()}}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </fieldset>
    <script>
            layui.use(['laydate','layer'], function(){
            var laydate = layui.laydate;
            var $ = layui.jquery, layer = layui.layer;
            //日期范围
            laydate.render({
                elem: '#sheng'
                ,range: '--'
            });
            laydate.render({
                elem: '#chu'
                ,range: '--'
            });
           $("#bb").click(function () {
               var sn = $(this).attr('sn');
               layer.prompt({title: '驳回原因', formType: 2}, function(text, index){
                   if(text){
                       $.post("{{url('admin/record/draw_false')}}", {sn:sn,describe:text}, function(re){
                           if(re == 1){
                               layer.msg('操作成功!');
                           }else {
                               layer.msg('操作失败!');
                           }
                           layer.close(index);
                           location.reload();
                       });
                   }
               });
           });
           $("#bb2").click(function () {
               var describe = $(this).attr('describe');
               layer.msg(describe);
           });

        });

    </script>
@stop
