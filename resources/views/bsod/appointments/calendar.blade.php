@extends('layouts.template')

@section('page_header')
<i class='icon-calendar '></i>
<span>{{trans('appointments.list_quotas')}}</span>
{!! HTML::style('assets/bower_resources/jquery-ui/themes/smoothness/jquery-ui.min.css');!!}
{!! HTML::style('assets/bower_resources/fullcalendar/dist/fullcalendar.min.css'); !!}
@stop

@section('page_content')

<div class="row">
    <div class="col-sm-12">
        <div class="box bordered-box blue-border" style="margin-bottom:0;">
            <div class="box-header {{ $color_bandeau }}">
                <div class="title">
                    {{trans('appointments.title')}}
                </div>
            </div>
            <div class="box-content">
                <div class='row-fluid'>
                    {!! $calendar->calendar() !!}
                 </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('page_js')
    {!! HTML::script('assets/bower_resources/jquery/dist/jquery.min.js') !!}
    {!! HTML::script('assets/bower_resources/moment/moment.js') !!}
    {!! HTML::script('assets/bower_resources/fullcalendar/dist/fullcalendar.min.js') !!}
   <!-- {!! HTML::script('assets/bower_resources/fullcalendar/dist/fullcalendar.js') !!}-->
    {!! HTML::script('assets/bower_resources/fullcalendar/dist/locale-all.js') !!}

    {!! $calendar->script() !!}

   <script>
    $(document).ready(function() {
        $('#{{$idcalendar}}').fullCalendar({
           lang: 'fr'
        });
    });
</script>
@stop




