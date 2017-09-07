@extends('common.layout')

@section('title', '关于')
@section('nav-about', 'nav-current')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @component('components/post')
                    @slot('title', '关于')

                    <blockquote>
                        <p>
                            Concerns 是一个数据抓取和聚合平台，可以从各大OJ抓取设置好的关注ID的提交记录、参赛记录等，形成类似SNS社区“好友动态”的功能以获悉好友在各大OJ的最近动态。
                        </p>
                    </blockquote>
                    <p>
                        支持的OJ：HDU OJ、Codeforces、POJ
                    </p>
                    <p>
                        服务器非常弱还请大家手下留情
                    </p>
                    <br/>
                    <div style="color: grey">
                        <p>
                            部分CSS来自：开源博客系统 <a href="http://www.ghostchina.com/">Ghost</a>
                        </p>
                        <p>
                            使用了图标库：<a href="http://fontawesome.io/">Font Awesome</a>, <a href="http://glyphicons.com/">Glyphicons</a>
                        </p>
                    </div>
                    <hr/>
                    <div align="center">
                        <p class="hidden-md hidden-lg">
                            <span class="fa fa-address-card fa-2x"></span>
                            <a href="#contact">Contact</a>
                        </p>
                        <p>
                            <span class="fa fa-github fa-2x"></span>
                            <a href="https://github.com/lytning98/concerns/" target="_blank">Github Repository</a>
                        </p>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-4" id="contact">
                @component('components/widget', ['rawHTML' => '<i class="fa fa-address-card"></i>'])
                    @slot('title', 'Contact')
                    <div align="center">
                        <img src="{{\App\Tools\Gravatar::getURLbyEmail('me@lytning.xyz', 75)}}" alt="author" style="border-radius:20px;"/>
                        <h4>Lytning</h4>
                        <h5 style="color:grey">咸鱼一条</h5>
                        <hr/>

                        <h4 style=""><i class="fa fa-envelope"></i> {!! \App\Tools\SysManager::emailObfuscationHTML('me@lytning.xyz') !!}</h4>
                        <h4 style=""><i class="fa fa-github"></i> lytning98</h4>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection