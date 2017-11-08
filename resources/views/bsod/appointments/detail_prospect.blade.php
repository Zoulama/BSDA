@extends('layouts.template')

@section('page_header')
<i class='icon-calendar '></i>
<span>Agenda de prise de rendez-vous</span>

{!! HTML::style('assets/bower_resources/jquery-ui/themes/smoothness/jquery-ui.min.css');!!}
{!! HTML::style('assets/bower_resources/fullcalendar/dist/fullcalendar.min.css'); !!}

@stop

@section('page_content')

<div class="row">
    <div class="col-sm-12">
        <div class="box bordered-box blue-border" style="margin-bottom:0;">
            <div class="box-header {{ $color_bandeau }}">
                <div class="title">
                    {!!trans('appointments.title')!!}
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
<div class="row">
 <form class="form form-horizontal" style="margin-bottom: 0;" method="post" action="#" accept-charset="UTF-8">
          <hr class='hr-normal'>

          <div class='form-group'>
              <label class='col-md-2 control-label' for='disabledInput1'>{!!trans('appointments.adresse')!!}</label>
              <div class='col-md-5'>
                  <label class="label label-success">{{ $params['adresse'] }}</label>
              </div>
          </div>

          <div class='form-group'>
              <label class='col-md-2 control-label' for='disabledInput1'>{!!trans('appointments.appointment_date')!!}</label>
              <div class='col-md-5'>
                  <label class="label label-success">{{ $params['date_horaire'] }}</label>
              </div>
          </div>

          <div class='form-group'>
              <label class='col-md-2 control-label' for='disabledInput1'>{!!trans('appointments.appointmentType')!!}</label>
                      <label class="label label-success">{{ $params['CalendarTypeDesc'] }}</label>
          </div>

          <div class='form-group'>
              <label class='col-md-2 control-label' for='disabledInput1'>{!!trans('appointments.client')!!}</label>
                      <label class="label label-success">{{ $params['bsodClient']->first_name  }}  {{ $params['bsodClient']->last_name  }}</label>
          </div>

         <div class='form-actions form-actions-padding' style='margin-bottom:0'>
               <a href="{{URL::route('Appointment.edit',[$params['id'],'prospect'])}}" class='btn-space'>
                     <button class='btn btn-primary {{ $color_form }} btn-lg btn-space' type='submit'>
                        <i class="icon-large icon-check-sign"></i>
                        {!!trans('appointments.change_appointmant')!!}
                    </button>
                </a>

                 <a href="{{URL::route('Appointment.delete',[$params['id'],'prospect'])}}" class='btn-space'>
                    <button class='btn {{ $color_form }} btn-lg btn-space btn-danger' type='submit'>
                        <i class="icon-remove"></i>
                        {!!trans('appointments.delete_appointment')!!}
                    </button>
                </a>

            </div>
        </div>
  </form>
</div>

@stop

@section('page_js')
   {!! HTML::script('assets/bower_resources/jquery/dist/jquery.min.js') !!}
    {!! HTML::script('assets/bower_resources/moment/moment.js') !!}
    {!! HTML::script('assets/bower_resources/fullcalendar/dist/fullcalendar.min.js') !!}
    {!! HTML::script('assets/bower_resources/fullcalendar/dist/locale-all.js') !!}

    {!! $calendar->script() !!}
   <script>
    $(document).ready(function() {
        $("#{{$params['idcalendar']}}").fullCalendar({
            locale: 'fr'
        });
    });
</script>
@stop




