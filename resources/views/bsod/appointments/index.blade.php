@extends('layouts.template')

@section('page_header')
<i class='icon-calendar'></i>
<span>Prise de rendez-vous</span>
{!! HTML::style('assets/bower_resources/jquery-ui/themes/smoothness/jquery-ui.min.css');!!}
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
                @if(Session::has('success_message'))
                   <div class="alert alert-success">
                      <button type="button" class="close" data-dismiss="success">&times;</button>
                      <strong> {{Session::get('success_message')}}</strong> <br>
                    </div>
                @endif
                @if(Session::has('error_message'))
                    <div class="alert alert-danger">
                      <button type="button" class="close" data-dismiss="danger">&times;</button>
                      <strong> {{Session::get('error_message')}}</strong> <br>
                    </div>
                @endif
                {!! Form::open(array('route' => array('Appointment.getcalandar'),'class' => 'form form-horizontal validate-form','id'=>'form_appointment')) !!}
                <input type="hidden" id="typeRDV" name="typeRDV" value="{{$typeRDV}}" />
                @if(isset($appointmentID))
                   <input type="hidden" id="appointmentID" name="appointmentID" value="{{$appointmentID}}" />
                @endif

                @if(isset($bsod_client_id))
                   <input type="hidden" id="bsod_client_id" name="bsod_client_id" value="{{$bsod_client_id}}" />
                @endif

                 @if(isset($client_id))
                   <input type="hidden" id="clientId" name="clientId" value="{{$client_id}}" />
                @endif

                @if(isset($ScheduleID))
                    <input type="hidden" id="ScheduleID" name="ScheduleID" value="{{$ScheduleID}}" />
                @endif
                 @if(isset($eligibilityAddress_id))
                    <input type="hidden" id="eligibilityAddress_id" name="eligibilityAddress_id" value="{{$eligibilityAddress_id}}" />
                @endif
                <div id="form_creation" >
                    <div class="box-content">
                        <div class="form-group">
                                <label class="col-md-3 control-label" for="identifiant_rv">{{trans('appointments.id_prise')}} :</label>
                                <div class="col-md-2 controls">
                                    {!! Form::text('accomodationId',$id_prise, array('id' => 'accomodationId' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('appointments.id_prise'))) !!}
                                </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="date_debut">{{trans('appointments.date_debut')}}: </label>
                            <div class="col-md-2 controls">
                                <div class="input-group date" data-provide="datepicker">
                                    {!! Form::text('startDate', '', array('id' => 'startDate' ,'required' => 'required','class' => 'date-picker form-control','placeholder'=>trans('appointments.date_debut'))) !!}
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="date_fin">{{trans('appointments.date_fin')}}: </label>
                            <div class="col-md-2 controls">
                                <div class="input-group date" data-provide="datepicker">
                                    {!! Form::text('endDate', '', array('id' => 'endDate' ,'required' => 'required','class' => 'date-picker form-control','placeholder'=>trans('appointments.date_fin'))) !!}
                                    <div class="input-group-addon">
                                        <span class="glyphicon glyphicon-th"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-3 control-label" for="type_rv"> Type de rendez-vous{{trans('appointments.type_rv')}} :</label>
                            <div class="col-md-2 controls">
                                <select id="appointmentType" name="appointmentType"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                    <option  selected> Type rendez-vous -- </option>
                                    @if(isset($typeRv))
                                        @foreach($typeRv as $value)
                                            <option value="{{$value}}">{{$value}}</option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                        </div>
                        <p>&nbsp;</p>
                        <div class="form-actions form-actions-padding">
                            <div class="row text-right">
                                <div class="col-sm-7 col-sm-offset-5">
                                    <button class='btn btn-primary {{ $color_form }} btn-lg ladda-button'  data-style="slide-left" type='submit'>
                                        <i class='icon-save'></i>
                                        {{trans('button/button.envoyer')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('page_js')
    <script type="text/javascript">
             $(function() {
            $('.date-picker').datepicker(
                    {
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true,
                        showButtonPanel: true,
                        onClose: function(dateText, inst) {
                            function isDonePressed(){
                                return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                            }
                            if (isDonePressed()){
                                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                                $(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

                                $('.date-picker').focusout()
                            }
                        },
                        beforeShow : function(input, inst) {

                            inst.dpDiv.addClass('month_year_datepicker')

                            if ((datestr = $(this).val()).length > 0) {
                                year = datestr.substring(datestr.length-4, datestr.length);
                                month = datestr.substring(0, 2);
                                $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
                                $(this).datepicker('setDate', new Date(year, month-1, 1));
                                $(".ui-datepicker-calendar").hide();
                            }
                        }
                    })
        });
    </script>
@stop