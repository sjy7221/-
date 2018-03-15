<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- 上述3个meta标签*必须*放在最前面，任何其他内容都*必须*跟随其后！ -->
    <title></title>

    <!-- Bootstrap -->
    <link href="{{asset('agent/css/bootstrap.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('agent/css/base.css')}}">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="{{asset('agent/js/html5shiv.min.js')}}"></script>
      <script src="{{asset('agent/js/respond.min.js')}}"></script>
    <![endif]-->
    <style>
        .content {
            padding: 0 15px;
        }

        .con .contents a {
            display: block;
        }
        .con .contents a p {
            text-align: center;
            font-size: 20px;
        }

    </style>
</head>

<body>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="{{asset('agent/js/jquery.min.js')}}"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="{{asset('agent/js/bootstrap.min.js')}}"></script>
    <nav class="navbar navbar-default">
        荆门棋牌
    </nav>
    <div class="content">
        <div class="balance clearfix">
            <span>用户名:{{session('nickname')}}</span>
            <span>账户佣金：{{$balance}}元</span>
        </div>
        @if (count($errors) > 0)
            <div class="alert alert-danger fade in" id="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li style="font-size: 20px;"><i class="fa fa-times"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <script>
                setTimeout(function(){
                    $("#error").slideToggle();
                },2000);
            </script>
        @endif
        @if (session('success')==1)
            <div class="alert alert-success fade in" id="success">
                <p style="font-size: 20px;"> <i class="fa fa-check"></i>{{session('msg')}}</p>
            </div>
            <script>
                setTimeout(function(){
                    $("#success").slideToggle();
                },1500);
            </script>
        @endif
        @yield('content')
    </div>

</body>

</html>