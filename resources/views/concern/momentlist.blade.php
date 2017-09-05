@forelse($dataset as $data)
    @php
        $moment = $data['moment'];
        $nickname = isset($data['parent'])?($data['parent']->nickname):'';
    @endphp
    @component('components/media')
        @if($moment->event != 'CONTEST_FLAG')
            @slot('title')
                <a href="{{route('profile', ['id'=>$data['parent']->id])}}">{{$nickname}}</a>
            @endslot
            @slot('grey', $data['username'] . '@' . $moment->oj)
            @slot('imgsrc', \App\Tools\Gravatar::getURLbyPerson($data['parent'], 45))
            @slot('imghref', route('profile', ['id' => $data['parent']->id]))

            @if($moment->event == 'AC')
                {{--<span class="label label-success">Accepted</span>--}} {{$nickname}} AC了 <a target="_blank" href="{{$moment->url or '#'}}">{{$moment->problem}}</a><br/> {!! $moment->description or ''!!}
            @else
                <span class="label label-warning">Rating</span> {{$nickname}}'s rating changed : {{$moment->description}} in {{$moment->problem}}
            @endif
            <br/>
        @else
            @php
                $contestants = $data['contestants']['data'];
            @endphp

            @slot('title')
                <a href="{{$moment->url}}" target="_blank">{{$moment->problem}}</a>
            @endslot
            @slot('grey', 'Finished')
            @slot('imgsrc', asset('image/cf.png'))
            @slot('imghref', "http://codeforces.com/")

            @if(count($contestants))
                <p>您的关注中有{{count($contestants)}}位参加了这场比赛。</p>
                <table class="table table-striped">
                    <tr>
                        <th>Friend</th>
                        <th>Solved</th>
                        @for($i = 0; $i <  count($contestants[0]['result']->presult); $i++)
                            <th>{{chr(ord('A')+$i)}}</th>
                        @endfor
                    </tr>
                    @foreach($contestants as $row)
                        <tr>
                            <td>{{$row['username']}}</td>
                            <td>{{$row['result']->solved}}</td>
                            @foreach($row['result']->presult as $pres)
                                @if($pres->solved)
                                    <td bgcolor="#57FF57">
                                        {{$pres->time}}
                                        <strong style="color:red"> {{$pres->fail?("(-" . $pres->fail . ")"):''}}</strong>
                                    </td>
                                @elseif($pres->fail)
                                    <td bgcolor="#FF5757"><strong>{{"(-" . $pres->fail . ")"}}</strong></td>
                                @else
                                    <td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            @else
                您的关注中无人参加这场比赛。
                <br/>
            @endif
        @endif

        <span style="color:grey">{{$moment->time}}</span>
    @endcomponent
    <hr />
    @break($loop->iteration == $ITEM_PER_PAGE)
@empty
    <div class="alert alert-info" role="alert">没有任何动态!</div>
@endforelse
@if($count <= $ITEM_PER_PAGE)
    <ul class="pagination">
        <li class="disabled"><span>&laquo;</span></li>
        <li class="active"><a href="#">1</a></li>
        <li class="disabled"><span>&raquo;</span></li>
    </ul>
@else
{!! $paginator->render() !!}
@endif
<br/>