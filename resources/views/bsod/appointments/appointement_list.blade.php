@extends('layouts.template')

@section('page_css')
<!-- / datatables -->
{!! HTML::style('assets/bower_resources/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css'); !!}
{!! HTML::style('assets/stylesheets/plugins/datatables/dataTables_filter.css'); !!}
{!! HTML::style('assets/bower_resources/datatables-scroller/css/dataTables.scroller.css'); !!}

@stop

@section('page_header')
<i class='icon-calendar'></i>
<span>{{trans('appointments.gestion_prise_rdv')}}</span>
@stop

@section('page_content')
<div class='row'>
    <div class='col-sm-12 col-md-12'>
        <div class='row recent-activity'>
            @if(Session::has('success_message'))
               <div class="alert alert-success">
                  <button type="button" class="close" data-dismiss="success">&times;</button>
                  <strong> {{Session::get('success_message')}}</strong> <br>
                </div>
            @endif
            @if(Session::has('error_message'))fdsfqfqsf
                    <div class="alert alert-danger">
                      <button type="button" class="close" data-dismiss="danger">&times;</button>
                      <strong> {{Session::get('error_message')}}</strong> <br>
                    </div>
            @endif
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-calendar'></i>
                            {{trans('appointments.liste_rdv')}}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <div class='box-content box-no-padding maindiv'>
                        <ul class='nav nav-tabs nav-tabs-simple'>
                            <li class='active'>
                                <a data-toggle="tab" class="green-border" href="#InProgress" id="tab-inprogress"><i class='icon-exchange text-green'></i>
                                    {{trans('appointments.rdv_en_cours')}}
                                </a>
                            </li>
                            <li>
                                <a data-toggle="tab" class="purple-border" href="#Archived" id="tab-deferred"><i class='icon-time text-purple'></i>
                                   {{trans('appointments.rdv_archive')}}
                                </a>
                            </li>
                        </ul>
                        <div class='tab-content portlet-body'>
                            <div class='tab-pane fade in active'>
                              <table id="table-main" class="table table-condensed table-striped table-hover" style='margin-bottom:0;' width="100%">
                                <thead>
                                  <tr>
                                    <th> {{ trans('appointments.ScheduleID')}} </th>
                                    <th> {{ trans('appointments.appointment_date')}} </th>
                                    <th> {{ trans('appointments.ShiftDesc')}} </th>
                                    <th> {{ trans('appointments.appointmentType')}} </th>
                                    <th> {{ trans('appointments.type')}} </th>
                                    <th> {{ trans('appointments.actions')}} </th>
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                              </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('page_js')

{!! HTML::script('assets/bower_resources/datatables/media/js/jquery.dataTables.js'); !!}
{!! HTML::script('assets/bower_resources/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.js'); !!}
{!! HTML::script('assets/bower_resources/datatables-scroller/js/dataTables.scroller.js'); !!}


<script type="text/javascript" src="/assets/bower_resources/datatables/jquery.dataTables.js"></script>

<script type="text/javascript" src="/assets/javascripts/table-responsive.js"></script>
<!-- jquery-timeago -->
{!! HTML::script('assets/bower_resources/jquery-timeago/jquery.timeago.js'); !!}
{!! HTML::script('assets/bower_resources/jquery-timeago/locales/jquery.timeago.fr-short.js'); !!}
<script type="text/javascript">

    $(document).ready(function() {
        $.extend( $.fn.dataTable.defaults, {
            "pageLength": 50,
            "serverSide": true,
            "processing": true,
            "stateSave": true,
            "searching": false,
            "language": {
                "url": "../assets/javascripts/plugins/datatables/i18n/French.lang"
            },
        });

        var search_value = 'active';
        var oTableMain = $('#table-main').DataTable({
            "columns": [
                { "name": "ScheduleID" },
                { "name": "appointment_date" },
                { "name": "ShiftDesc" },
                { "name": "appointmentType" },
                { "name": "type" },
                { "name": "action" }
            ],
            "ajax": {
                "url": "{{ URL::route('Appointment.datatable') }}",
                "data": function (d) {
                    d.filter = search_value;
                },
            },
            "ordering": true,
            "dom": "rtSi",
            "scrollY": "370",
            "drawCallback": function( oSettings ) {
                $("abbr.timeago").timeago();
            },
        });

        $('#tab-inprogress').bind('click', function (e) {
            search_value = 'active';
            oTableMain.ajax.reload();
        });
        $('#tab-deferred').bind('click', function (e) {
            search_value = 'archived';
            oTableMain.ajax.reload();
        });


        setInterval( function () {
            oTableMain.ajax.reload();
        }, 300000 );
    });
</script>
@stop