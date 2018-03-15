@extends('admin.layouts')
@section('content')
        <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
            <legend>日志记录</legend>
                <div class="layui-field-box">
                    <table class="layui-table">
                        <colgroup>
                            <col width="500">
                            <col width="100">
                        </colgroup>
                        <thead>
                        <tr>
                            <th>操作</th>
                            <th>时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $v)
                            <tr>
                                <td>{{$v->title}}</td>
                                <td >{{$v->time}}</td>
                        @endforeach
                        <tr>
                            <td colspan="2" class="page">{{$data->links()}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
        </fieldset>

@stop
