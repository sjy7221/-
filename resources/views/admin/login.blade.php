<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Mosaddek">
    <meta name="keyword" content="FlatLab, Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <link rel="shortcut icon" href="img/favicon.html">
    <link rel="stylesheet" href="{{url("./layui/css/bootstrap.min.css")}}">
    <title>后台登入</title>
	<style type="text/css">

    #recode{
	cursor:pointer
    }
    #yzm{
    float:right;
    margin-top:2px;
    margin-right:40px;
    }
    .new-style-input{
        padding: 0 15px!important;
        background: rgba(45,45,45,.15)!important;
        -moz-border-radius: 6px!important;
        -webkit-border-radius: 6px!important;
        border-radius: 6px!important;
        border: 1px solid #3d3d3d!important;
        border: 1px solid rgba(255,255,255,.15)!important;
        -moz-box-shadow: 0 2px 3px 0 rgba(0,0,0,.1) inset!important;
        -webkit-box-shadow: 0 2px 3px 0 rgba(0,0,0,.1) inset!important;
        box-shadow: 0 2px 3px 0 rgba(0,0,0,.1) inset!important;
        font-size: 14px!important;
        color: #fff!important;
        width: 290px!important;
        height: 42px!important;
    }
    .new-style-input2{
        width:60%!important;
    }
    .form-signin {
        max-width: 330px;
        margin: 100px auto 0;
        background: #fff;
        border-radius: 5px;
        -webkit-border-radius: 5px;
    }
    .form-signin h2.form-signin-heading {
        margin: 0;
        padding: 20px 15px;
        text-align: center;
        background: #41cac0;
        border-radius: 5px 5px 0 0;
        -webkit-border-radius: 5px 5px 0 0;
        color: #fff;
        font-size: 18px;
        text-transform: uppercase;
        font-weight: 300;
        font-family: 'Open Sans', sans-serif;
    }
    .form-signin input[type="text"], .form-signin input[type="password"] {
        margin-bottom: 15px;
        border-radius: 5px;
        -webkit-border-radius: 5px;
        border: 1px solid #eaeaea;
        box-shadow: none;
        font-size: 12px;
    }
    .form-signin .btn-login {
        background: #f67a6e;
        color: #fff;
        text-transform: uppercase;
        font-weight: 300;
        font-family: 'Open Sans', sans-serif;
        box-shadow: 0 4px #e56b60;
        margin-bottom: 20px;
        width: 287px;
    }
</style>
    <link rel="stylesheet" href="{{url('layui/css/layui.css')}}">
    <script src="{{url("layui/layui.js")}}"></script>
</head>

  <body class="login-body" style="background: url(../images/bc.jpg)">
    <div>
      <form class="form-signin" method="post" action="{{url('admin/login')}}" style="background:none;">
          {{ csrf_field()}}
        <h2 class="form-signin-heading" style="background:none;">游戏后台管理系统</h2>
        <div class="login-wrap">

            <input type="text" name="name" class="form-control new-style-input" placeholder="用户名" autofocus>
            <input type="password" name="password" class="form-control new-style-input" placeholder="密码">
            <input type="text"  name="code" class="form-controla new-style-input2 new-style-input" placeholder="验证码" style="float:left">

            <div id="yzm" >
            	<a href="javascript:;" id="recode1" title="看不清？换一张！"><img src="{{url('verify')}}" border="0" class="verifyimg" id="verifyimg" /></a>
			</div>

            <button id="btn-submit" class="btn btn-lg btn-login btn-block"  type="submit" >登录</button>

        </div>

      </form>

    </div>

<div class="copyright" style="position:absolute;bottom:20px;width: 100%;text-align: center;"></div>
    <script>
	var recode = document.getElementById('recode1');
	var verifyimg = document.getElementById('verifyimg');
		recode.onclick = function(){
			var time = new Date().getTime();
			verifyimg.src = "{{url('verify')}}?_="+time;

		}
    localStorage.setItem("list",'');
    layui.use('layer', function(){
        @if (count($errors) > 0)
            @foreach ($errors->all() as $error)
              layer.msg("{{$error}}");
        @endforeach
    @endif
    @if (session('success')==1)
        layer.msg("{{session('msg')}}");
        @endif
    });
	</script>
  </body>
</html>
