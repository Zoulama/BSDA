<div class="box">
    @foreach ($boutons as $bouton)
    <a data-toggle="modal" href="#addComment" class="btn {{$bouton['class']}}" id="{{$bouton['id']}}">{{$bouton['title']}}</a>
    @endforeach
    @if (sizeof($autres))
    <!-- Other action proposal -->
    <div class="btn-group dropdown">
        <button class="btn dropdown-toggle" data-toggle="dropdown">
            {{{Lang::get('ticket/ticket_lang.bouton.other')}}}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu pull-right">
            @foreach ($autres as $autre)
            <li><a data-toggle="modal" href="#addComment" id="{{$autre['id']}}">{{$autre['title']}}</a></li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
