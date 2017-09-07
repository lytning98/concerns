<div class="panel panel-{{$style or 'success'}}">
    <div class="panel-heading">{!! $rawHTML or '' !!} {{$title or ''}}</div>
    <div class="panel-body">
        {{$slot}}
    </div>
</div>