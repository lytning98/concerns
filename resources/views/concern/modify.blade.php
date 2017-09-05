@extends('common.layout')

@section('title', $title)
@section('nav-concerns', 'nav-current')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @component('components/widget')
                    @slot('title', $title)

                    @if(isset($hasError))
                        {{-- handle error --}}
                        @component('components/panel')
                            @slot('title', '错误')
                            @slot('style', 'danger')
                            {{$errMsg}}
                        @endcomponent
                        <div align="center">
                            <button class="btn btn-default btn-lg" onclick="window.location.href='{{route('concern')}}'">返回</button>
                        </div>
                    @else
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('concern-update') }}">
                            {{csrf_field()}}
                            <input type="hidden" name="modifying" value="{{$id or 0}}"/>
                            {{-- rendering form with generated data passed by controller --}}
                            @foreach($data as $now)
                                <div class="form-group{{ $errors->has($now['attr_dot']) ? ' has-error' : '' }}">
                                    <label for="{{$now['attr']}}" class="col-md-4 control-label">{{$now['name']}}</label>

                                    <div class="col-md-6">
                                        <input id="{{$now['attr']}}" type="text" class="form-control" name="{{$now['attr']}}"
                                               value="{{ $now['ori'] or old($now['attr']) }}"
                                               {{$loop->first?'required autofocus':''}}>

                                        @if ($errors->has($now['attr_dot']))
                                            <span class="help-block">
                                                <strong>{{ $errors->first($now['attr_dot']) }}</strong>
                                            </span>
                                        @endif
                                        @if ($loop->index == 1){{--Email Extra help-block--}}
                                            <span class="help-block hidden-md hidden-lg">
                                                关于E-Mail地址的相关说明请查看<a href="#notification">【本页底部提示】</a>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- add separating line between accounts settings and profile settings --}}
                                @if($loop->index == 1)
                                    <hr/>
                                @endif
                            @endforeach
                            <hr/>
                            <div class="form-group" id="formButtons">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-default" onclick="
                                        document.getElementById('progressBar').style.display='';
                                        document.getElementById('formButtons').style.display='none';
                                    ">
                                        保存
                                    </button>
                                    @if(isset($id))
                                    <a tabindex="0" class="btn btn-danger" role="button" data-toggle="popover" data-trigger="focus"
                                       data-html="true" title="<z>确认删除?</z>"
                                       data-content="<z><a href='{{route('concern-delete', ['id'=>$id])}}' class='btn btn-danger'>确认</a></z>">
                                        删除
                                    </a>
                                    @endif
                                </div>
                            </div>
                            <div class="progress" id="progressBar" style="display: none">
                                <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" style="width: 100%">
                                    正在抓取初始数据
                                </div>
                            </div>
                        </form>
                    @endif
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components/widget')
                    @slot('title', '提示')
                    <div id="notification">
                        <li>您的关注列表仅本人可见。</li>
                        <li>Email地址<strong>只用来</strong>获取Gravatar头像，<strong>不会向该地址发送任何邮件</strong>。本项信息为选填。</li>
                        <li>各OJ用户名可填写多个，使用符号“|”隔开（无引号）</li>
                        <li><strong>关于Email地址与隐私</strong>：</li>
                        <ul>
                            <li>输入的Email地址信息<strong>只会展示给信息提供者</strong>，网络爬虫或其他用户都<strong>无权限</strong>获取包含Email信息的response</li>
                            <li>同时Email地址在显示时<strong>也会经过一定的HTML混淆处理</strong>。</li>
                        </ul>
                    </div>
                    <hr/>
                    <div align="center">
                        <a class="btn btn-default" href="{{route('concern')}}">返回关注列表</a>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection