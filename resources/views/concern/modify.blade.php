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
                            @php
                                $data = [
                                    ['attr' => 'Person.nickname', 'name' => 'Nickname', 'ori' => $modifying?$nickname:''],
                                    ['attr' => 'Person.email', 'name' => 'E-Mail 地址', 'ori' => $modifying?$email:''],
                                   ];
                                $oj = ['HDU', 'Codeforces', 'POJ', 'VJUDGE'];
                                foreach ($oj as $cur){
                                    array_push($data, ['attr' => $cur, 'name' => $cur . '用户名', 'ori'=>'']);
                                }
                                if($modifying)
                                {
                                    foreach ($accounts as $acc)
                                    {
                                        for($i = 0; $i < count($data); $i++)
                                        {
                                            if($data[$i]['attr'] == $acc->oj)
                                            {
                                                $data[$i]['ori'] = $acc->username;
                                                break;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @foreach($data as $now)
                                <div class="form-group{{ $errors->has($now['attr']) ? ' has-error' : '' }}">
                                    <label for="{{$now['attr']}}" class="col-md-4 control-label">{{$now['name']}}</label>

                                    <div class="col-md-6">
                                        <input id="{{$now['attr']}}" type="text" class="form-control" name="{{$now['attr']}}"
                                               value="{{ $now['ori'] or old($now['attr']) }}" required autofocus>
                                        @if ($errors->has($now['attr']))
                                            <span class="help-block">
                                                <strong>{{ $errors->first($now['attr']) }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($loop->index == 1)
                                    <hr/>
                                @endif
                            @endforeach
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
                        <button class="btn btn-default">返回关注列表</button>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection