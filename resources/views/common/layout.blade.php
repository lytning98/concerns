<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ env('APP_NAME') }} - @yield('title', 'default')</title>
    {{--<link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" rel="stylesheet">--}}
    <link href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/index.css') }}" rel="stylesheet">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{----}}
    <link rel="stylesheet" href="//cdn.bootcss.com/font-awesome/4.3.0/css/font-awesome.min.css">
    {{----}}
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body class="home-template">

<header class="main-header"  style="background-image: url({{asset('image/head.jpg')}})">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                {{-- fecth gravatar logo --}}
                @if(Auth::check())
                    <a class="branding" href="#" title="{{Auth::user()->name}}">
                        <img src="{{\App\Tools\Gravatar::getURLbyUser(Auth::user(), 100)}}" alt="HEAD_PIC" style="border-radius:20px;">
                    </a>
                @else
                    <a class="branding" href="#" title="{{env('APP_NAME')}}"><img src="{{asset('image/logo.png')}}" alt="{{env('APP_NAME')}}"></a>
                @endif
            </div>
        </div>
    </div>
</header>
<!-- start navigation -->
<nav class="main-navigation">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="navbar-header">
                        <span class="nav-toggle-button collapsed" data-toggle="collapse" data-target="#main-menu">
                        <span class="sr-only">Toggle navigation</span>
                        <i class="fa fa-bars"></i>
                        </span>
                </div>
                <div class="collapse navbar-collapse" id="main-menu">
                    <ul class="menu">
                        <li class="@yield('nav-index')" role="presentation"><a href="{{url('/')}}">首页</a></li>

                        @if(Auth::check())
                            <li class="@yield('nav-concerns')" role="presentation"><a href="{{route('concern')}}">我的关注</a></li>
                            <li  role="presentation" class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{Auth::user()->name}} <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#">个人资料</a></li>
                                    <li><a href="{{route('logout')}}">退出登录</a></li>
                                </ul>
                            </li>
                        @else
                            <li class="@yield('nav-login')"  role="presentation"><a href="{{url('login')}}">登录</a></li>
                            <li class="@yield('nav-register')" role="presentation"><a href="{{url('register')}}">注册</a></li>
                        @endif

                        <li class="@yield('nav-about')" role="presentation"><a href="#">关于</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- end navigation -->
<div class="container" witdh="80%">
    @yield('content')

    <footer class="post-footer clearfix">
        <div class="pull-right tag-list">
            <i class="fa fa-folder-open-o"></i>
            <p>Platform Version : xxx<br/>
                Server Time : {{time()}}<br/>
                Copyright information</p>
        </div>
    </footer>
</div>

<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(function () {
        $('[data-toggle="popover"]').popover()
    })
</script>
</body>
</html>