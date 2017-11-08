@extends('layouts.template')

@section('page_header')
<i class='icon-inbox'></i>
<span>Liste commande</span>
{!! HTML::style('/assets/bower_resources/jquery-ui/themes/smoothness/jquery-ui.min.css');!!}
<link rel="stylesheet" type="text/css" href="/assets/css/dataTables.bootstrap.css">
<link rel="stylesheet" type="text/css" href="/assets/css/tables.css">
@stop

@section('page_content')
<div class="box-content">
    @if(Session::has('success_message'))
       <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="success">&times;</button>
          <strong> {{Session::get('success_message')}}</strong> <br>
        </div>
    @endif
     @if(Session::has('success_message'))
       <div class="alert alert-success">
          <button type="button" class="close" data-dismiss="success">&times;</button>
          <strong> {{Session::get('success_message')}}</strong> <br>
        </div>
    @endif
    <!-- row-->
    <div class="row">
        <div class="portlet box info">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-inbox" data-name="rocket" data-size="16" data-loop="true" data-c="#fff" data-hc="white"></i> Commandes
                </div>
            </div>
            <div class="portlet-body table-responsive">
                <table class="table table-striped table-bordered" id="table2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Date commande</th>
                            <th>Type commande</th>
                            <th>Etat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if(isset($orders))
                        @foreach ($orders as $value)
                            <tr>
                                <td>{{$value['id_com']}}.</td>
                                <td>{{$value['externalSubscriberId']}}</td>
                                <td>{{$value['dateCommande']}}</td>
                                <td>{{$value['typeCommande']}} : <span class='label label-success'>{{$label_typeC[$value['typeCommande']]}}</span></td>
                                <td><span class='label label-success'>Success</span></td>
                                <td>
                                    <div class='text-right'>
                                    <a class='btn btn-success btn-mini' href="{{URL::route('Appointment.index',[$value['accomodationId'],'costumer'])}}">
                                        <i class='icon-calendar'></i> Prise rendez-vous
                                      </a>
                                      <a class='btn btn-info btn-mini' href="{{URL::route('Orders.View',[$value['id'],$value['id_com']])}}">
                                        <i class='icon-eye-open'></i>
                                      </a>
                                      <a class='btn btn-primary btn-mini'  href="{{URL::route('Orders.Edit',[$value['id'],$value['id_com']])}}">
                                        <i class='icon-edit'></i>
                                      </a>
                                      <a class='btn btn-danger btn-mini' href="">
                                        <i class='icon-remove'></i>
                                      </a>
                                    </div>
                                 </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>  <!-- row-->
</div>
@stop

@section('page_js')
<script src="/assets/js/app.js" type="text/javascript"></script>
<script type="text/javascript" src="/assets/bower_resources/datatables/jquery.dataTables.js"></script>
<script type="text/javascript" src="/assets/bower_resources/datatables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="/assets/bower_resources/datatables/dataTables.responsive.js"></script>
<script type="text/javascript" src="/assets/javascripts/table-responsive.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#table').DataTable( {
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.13/i18n/French.json"
        },
        "bDestroy": true,
    } );
} );
</script>
@stop
