@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>充值管理</legend>
                <div class="layui-field-box">
                    <table class="layui-table">
                        <colgroup>
                            <col width="50">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                            <col width="100">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>排序</th>
                            <th>钻石</th>
                            <th>价格</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $v)
                            <tr>
                                <td>{{$v->sort}}</td>
                                <td >{{$v->num}}</td>
                                <td >{{$v->money}}元</td>
                                <td >
                                    @if($v->is_show ==0 )
                                        <b style="color: #05ff40">显示</b>
                                    @else
                                        <b style="color: #ff0604">不显示</b>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{url("admin/goods/edit_show/{$v->id}")}}"  title="操作"><button class="layui-btn layui-btn-radius layui-btn-primary">操作</button></a>
                                </td>
                        @endforeach
                        </tbody>
                    </table>
                </div>
        </fieldset>

@stop
