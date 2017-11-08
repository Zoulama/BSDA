@extends('layouts.template')

@section('page_css')
<!-- / datatables -->
{!! HTML::style('assets/bower_resources/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css'); !!}
{!! HTML::style('assets/stylesheets/plugins/datatables/dataTables_filter.css'); !!}
{!! HTML::style('assets/bower_resources/datatables-scroller/css/dataTables.scroller.css'); !!}

@stop

@section('page_header')
<i class='icon-user'></i>
<span>{{trans('clientbsod.title')}}</span>
@stop

@section('page_content')
<div class="row">
    <div class="col-sm-12">
      <div class="box">
        <div class="box-header box-header-small blue-background">
          <div class="title">
            <div class="icon-search"></div>
            {{ Lang::get('bsod.Filter.title_search') }}
          </div>
          <div class="actions">
            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i></a>
          </div>
        </div>
        <div class="box-content">
            <fieldset class="form form-horizontal" id="filters">
                <table width="100%">
                    <tr>
                         <td>
                            <label class="col-md-3 control-label" style="padding-left:0;">{{{Lang::get('clientbsod.last_name')}}}</label>
                            <div class="col-md-8 controls" style="padding-left:0;">
                                    <input type="text" class="form-control input-sm" id="last_name" placeholder="{{{Lang::get('clientbsod.last_name')}}}">
                            </div>
                        </td>
                        <td>
                            <label class="col-md-3 control-label" style="padding-left:0;">{{{Lang::get('clientbsod.externalSubscriberId')}}}</label>
                            <div class="col-md-8 controls" style="padding-left:0;">
                                    <input type="text" class="form-control input-sm" id="search" placeholder="{{{Lang::get('clientbsod.externalSubscriberId')}}}">
                            </div>
                        </td>
                        <td>
                            <div class="pull-right" style="padding: 5px;margin-right: 8px;">
                                <button class="btn btn-primary btn-sm" type="button" id="search_bsod_client">{{{Lang::get('commande.search')}}}</button>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </div>
      </div>
    </div>
</div>
<div class='row'>
    <div class='col-sm-12 col-md-12'>
        <div class='row recent-activity'>
        @if(Session::has('success_message'))
           <div class="alert alert-success" role="alert">
              <button type="button" class="close" data-dismiss="success">&times;</button>
              <strong> {{Session::get('success_message')}}</strong> <br>
            </div>
        @endif
        @if(Session::has('error_message'))
        testetete
                <div class="alert alert-danger" role="alert">
                  <button type="button" class="close" data-dismiss="danger">&times;</button>
                  <strong> {{Session::get('error_message')}}</strong> <br>
                </div>
            @endif
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-list'></i>
                            {{trans('clientbsod.list')}}
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
                                    <th> {{ trans('clientbsod.externalSubscriberId')}} </th>
                                    <th> {{ trans('clientbsod.first_name')}} </th>
                                    <th> {{ trans('clientbsod.last_name')}} </th>
                                    <th> {{ trans('clientbsod.updated_at')}} </th>
                                    <th> {{ trans('clientbsod.actions')}} </th>
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

        var oTableMain = $('#table-main').DataTable({
            "columns": [
                { "name": "externalSubscriberId" },
                { "name": "first_name" },
                { "name": "last_name" },
                { "name": "updated_at" },
                { "name": "action" }
            ],
            "ajax": {
                "url": "{{ URL::route('ClientBsod.datatable') }}",
                 "data": function ( d ) {
                    d.last_name = $('#last_name').val(),
                    d.externalSubscriberId = $('#search').val()
                },
            },
            "ordering": true,
            "dom": "rtSi",
            "scrollY": "370",
            "drawCallback": function( oSettings ) {
                $("abbr.timeago").timeago();
            },
        });

        $( "#search_bsod_client").click(function() {
            oTableMain.ajax.reload();
        });

        setInterval( function () {
            oTableNew.ajax.reload();
            oTableMyTickets.ajax.reload();
            oTableMain.ajax.reload();
        }, 300000 );
    });
</script>
@stop
