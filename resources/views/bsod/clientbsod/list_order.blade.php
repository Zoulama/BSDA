@extends('layouts.template')

@section('page_css')
<!-- / datatables -->
{!! HTML::style('assets/bower_resources/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css'); !!}
{!! HTML::style('assets/stylesheets/plugins/datatables/dataTables_filter.css'); !!}
{!! HTML::style('assets/bower_resources/datatables-scroller/css/dataTables.scroller.css'); !!}

@stop

@section('page_header')
<i class='icon-user'></i>
<span>{{trans('clientbsod.list_orders')}}</span>
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
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-list'></i>
                            {{trans('clientbsod.list_orders')}}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <div class='box-content box-no-padding maindiv'>
                        <div class='tab-content portlet-body'>
                            <div class='tab-pane fade in active'>
                              <table id="table-main" class="table table-condensed table-striped table-hover" style='margin-bottom:0;' width="100%">
                                <thead>
                                  <tr>
                                    <th> {{ trans('orders.numCommande')}} </th>
                                    <th> {{ trans('orders.dateCommande')}} </th>
                                    <th> {{ trans('orders.typeCommande')}} </th>
                                    <th> {{ trans('orders.updated_at')}} </th>
                                    <th> {{ trans('orders.actions')}} </th>
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

        var search_value = 'in_progress';
        var oTableMain = $('#table-main').DataTable({
            "columns": [
                { "name": "numCommande" },
                { "name": "dateCommande" },
                { "name": "typeCommande" },
                { "name": "updated_at" },
                { "name": "action" }
            ],
            "ajax": {
                "url": "{{ URL::route('ClientBsod.listOrderDatatable',array($dataClient['id'])) }}",
            },
            "ordering": true,
            "dom": "rtSi",
            "scrollY": "370",
            "drawCallback": function( oSettings ) {
                $("abbr.timeago").timeago();
            },
        });

        setInterval( function () {
            oTableNew.ajax.reload();
            oTableMyTickets.ajax.reload();
            oTableMain.ajax.reload();
        }, 300000 );
    });
</script>
@stop
