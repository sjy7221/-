@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>意见反馈</legend>
                <div class="layui-field-box">
                    <table class="layui-table">
                        <colgroup>
                            <col width="50">
                            <col width="500">
                            <col width="100">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>手机号</th>
                            <th>反馈</th>
                            <th>时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $v)
                            <tr>
                                <td>{{$v->phone}}</td>
                                <td>{{$v->content}}</td>
                                <td >{{$v->time}}</td>
                        @endforeach
                        <tr>
                            <td colspan="3" class="page">{{$data->links()}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
        </fieldset>

@stop
