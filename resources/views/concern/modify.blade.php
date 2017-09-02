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
                                    </div>
                                </div>
                                {{-- add separating line between accounts settings and profile settings --}}
                                @if($loop->index == 1)
                                    <hr/>
                                @endif
                            @endforeach
                            <hr/>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-default">
                                        保存
                                    </button>
                                    @if(isset($id))
                                    <a tabindex="0" class="btn btn-danger" role="button" data-toggle="popover" data-trigger="focus"
                                       data-html="true" title="<z>确认删除?</z>"
                                       data-content="<z><a href='{{url('concern/delete/'.$id)}}' class='btn btn-danger'>确认</a></z>">
                                        删除
                                    </a>
                                    @endif
                                </div>
                            </div>

                        </form>
                    @endif
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components/widget')
                    @slot('title', '提示')
                    <li>Email地址<strong>只用来</strong>获取Gravatar头像，不会向该地址发送任何邮件。</li>
                    <li>各OJ用户名可填写多个，使用符号“|”隔开（无引号）</li>
                    <hr/>
                    <div align="center">
                        <a class="btn btn-default" href="{{route('concern')}}">返回关注列表</a>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection