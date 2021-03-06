@extends('common.layout')

@section('title', '我的关注')
@section('nav-concerns', 'nav-current')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @component('components/widget')
                    @slot('title')
                        我的关注
                        <button class="btn btn-default" onclick="window.location.href='{{route('concern-add')}}'">
                            <i class="fa fa-user-plus"><z> 添加</z></i>
                        </button>
                    @endslot

                    @if(session('msg'))
                        @component('components/alert')
                            @slot('style', session('msgStyle')==''?'success':session('msgStyle'))
                            {{session('msg')}}
                        @endcomponent
                    @endif

                    @forelse($data as $per)
                        @component('components/media')
                            @slot('imgsrc', \App\Tools\Gravatar::getURLbyPerson($per, 60))
                            @slot('imghref', route('profile', ['id'=>$per->id]))
                            @slot('title')
                                <a href="{{route('profile', ['id'=>$per->id])}}">{{$per->nickname}}</a>
                            @endslot
                            @slot('rawHTML', true)
                            @slot('grey', $per->getEmailHTML())
                            @slot('right')
                                <a class="btn btn-default btn-lg" href="{{route('concern-modify', ['id'=>$per->id])}}">
                                    <span class="glyphicon glyphicon-pencil"><z> 编辑</z></span>
                                </a>
                            @endslot
                            @forelse($per->accounts->groupBy('oj')->toArray() as $oj=>$accs)
                                {{$oj}}账号
                                @foreach($accs as $acc)
                                    [{{$acc['username']}}]
                                @endforeach
                                @if(!$loop->last)
                                    ，
                                @endif
                            @empty
                                还没有设置OJ账号
                            @endforelse
                        @endcomponent
                    @empty
                        <div class="alert alert-warning" role="alert">还未关注任何人！</div>
                    @endforelse

                    <hr/>
                    @if($count<=$ITEM_PER_PAGE)
                        {{--仅有一页时Paginator不显示页码 实在丑 手动补之--}}
                        <ul class="pagination">
                            <li class="disabled"><span>&laquo;</span></li>
                            <li class="active"><a href="#">1</a></li>
                            <li class="disabled"><span>&raquo;</span></li>
                        </ul>
                    @else
                        {!! $data->render() !!}
                    @endif
                    <br/>
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components/widget')
                    @slot('rawHTML', '<i class="fa fa-users"></i>')
                    @slot('title', '统计')
                    <p>共关注了{{count($data)}}人</p>
                    {{--may affect efficiency--}}
                    <p>好友动态{{\App\Tools\MmtManager::getMomentsCount(Auth::user())}}条</p>
                @endcomponent

            </div>
        </div>
    </div>
@endsection