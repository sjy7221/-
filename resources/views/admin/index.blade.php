@extends('admin.layouts')
@section('content')
    <script src="{{asset('echarts/echarts.min.js')}}"></script>
     <fieldset class="layui-elem-field site-demo-button" style="margin-top: 10px;">
        <legend>首页</legend>
        <div class="layui-field-box">
            <blockquote class="layui-elem-quote layui-quote-nm">
               欢迎您回来,{{$data['name']}}  &nbsp;&nbsp;&nbsp;上次登入时间：{{$data['last_time']}} &nbsp;&nbsp;&nbsp;上次登入IP：{{$data['last_ip']}} &nbsp;&nbsp;&nbsp; 上次登入地点：{{$data['last_address']}}
            </blockquote>
            <blockquote class="layui-elem-quote layui-quote-nm">
                平台总数据：&nbsp; &nbsp;  &nbsp; 总玩家数:{{$num1}}人     &nbsp; &nbsp;  &nbsp;      总代理人数： {{$num2}}人     &nbsp; &nbsp;  &nbsp;    代理总佣金： {{$num3}}元     &nbsp; &nbsp;  &nbsp;    总充值金额：{{$num4}}元  &nbsp; &nbsp;  &nbsp;     总提现金额：{{$num5}}元
            </blockquote>
            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                <legend>用户统计</legend>
                <br>
                <div class="layui-input-inline">
                    <select name="year" id="year">
                        @for($i=1;$i<=date('n');$i++)
                        <option value="{{$i}}" @if(date('n')==$i) selected @endif>{{$i}}月</option>
                        @endfor
                    </select>
                    &nbsp; &nbsp; &nbsp;&nbsp;玩家新增人数: <b id="num1">0</b>  &nbsp;&nbsp;&nbsp;代理新增人数：<b id="num2">0</b>
                </div>
                <div >
                    <div id="people" style="width: 1500px;height:500px;margin: 0 auto;"></div>
                </div>
            </fieldset>

            <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
                <legend>流水统计</legend>
                <br>
                <div class="layui-input-inline">
                    <select name="year" id="year2">
                        @for($i=1;$i<=date('n');$i++)
                            <option value="{{$i}}" @if(date('n')==$i) selected @endif>{{$i}}月</option>
                        @endfor
                    </select>
                    &nbsp; &nbsp; &nbsp;&nbsp;充值总金额: <b id="num3">0</b>  &nbsp;&nbsp;&nbsp;提现总金额：<b id="num4">0</b>
                </div>
                <div >
                    <div id="money" style="width: 1500px;height:500px;margin: 0 auto;"></div>
                </div>
            </fieldset>

        </div>
    </fieldset>
    <script type="text/javascript">
        layui.use('layer', function(){
            var $ = layui.$
            //用户统计
            var month =  $("#year").val();
            people(month);
            $("#year").change(function () {
                people($("#year").val());
            });
            function people(month) {
                $.post("{{url('admin/getPeople')}}", {month:month}, function(re){
                    $("#num1").text(re.num1);
                    $("#num2").text(re.num2);
                   // 基于准备好的dom，初始化echarts实例
                    var myChart1 = echarts.init(document.getElementById('people'));
                    // 指定图表的配置项和数据
                    var option1 = {
                        title: {
                            text: month+'月用户新增记录'
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['新增会员','新增代理']
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: re.days
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [
                            {
                                name: '新增会员',
                                type: 'line',
                                stack: '总量',
                                data: re.members
                            },
                            {
                                name: '新增代理',
                                type: 'line',
                                stack: '总量',
                                data: re.agent
                            }
                        ]
                    };
                    // 使用刚指定的配置项和数据显示图表。
                    myChart1.setOption(option1);
                });
            }


            //金额统计
            var month2 =  $("#year2").val();
             money(month2);
            $("#year2").change(function () {
                money($("#year2").val());
            });
            function money(month) {
                $.post("{{url('admin/getMoney')}}", {month:month}, function(re){
                    $("#num3").text(re.num1);
                    $("#num4").text(re.num2);
                    // 基于准备好的dom，初始化echarts实例
                    var myChart2 = echarts.init(document.getElementById('money'));
                    // 指定图表的配置项和数据
                    var option2 = {
                        title: {
                            text: month+'月流水统计'
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['充值金额','提现金额']
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            data: re.days
                        },
                        yAxis: {
                            type: 'value'
                        },
                        series: [
                            {
                                name: '充值金额',
                                type: 'line',
                                stack: '总量',
                                data: re.recharge
                            },
                            {
                                name: '提现金额',
                                type: 'line',
                                stack: '总量',
                                data: re.record
                            }
                        ]
                    };
                    // 使用刚指定的配置项和数据显示图表。
                    myChart2.setOption(option2);
                });
            }

        });

    </script>
@stop