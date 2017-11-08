@extends('layouts.template')

@section('page_header')
<i class='icon-map-marker'></i>
<span>{!!trans('eligibilityadress.info_offre')!!} {{$eligibilityAddress->street_number_complement}}</span>
{!! HTML::style('/assets/bower_resources/jquery-ui/themes/smoothness/jquery-ui.min.css');!!}
<link rel="stylesheet" type="text/css" href="/assets/css/dataTables.bootstrap.css">
<link rel="stylesheet" type="text/css" href="/assets/css/tables.css">
@stop

@section('page_content')
<div class='row'>
    <div class='col-sm-12 col-md-12'>
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-icon-map-marker text-green'></i>
                             {!!trans('eligibilityadress.adress')!!}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <div class='box-content box-no-padding maindiv'>
                        <div class='tab-content'>
                            <div class='tab-pane fade in active'>
                              <table id="table-main" class="table table-condensed table-hover" style='margin-bottom:0;' width="100%">
                                <thead>
                                  <tr>
                                    <th width="11%">{!!trans('eligibilityadress.accomodationId')!!}</th>
                                    <th width="23%">{!!trans('eligibilityadress.zipcode')!!}</th>
                                    <th width="36%">{!!trans('eligibilityadress.street_number_complement')!!}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                 <tr>
                                    <td>{{$eligibilityAddress->accomodationId}}</td>
                                    <td>{{$eligibilityAddress->zipcode }}</td>
                                    <td>{{$eligibilityAddress->street_number_complement}}</td>
                                </tr>
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
<div class='row'>
    <div class='col-sm-12 col-md-6'>
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-signal text-warning'></i>
                             {!!trans('eligibilityadress.offres')!!}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <div class='box-content box-no-padding smalldiv'>
                        <div class='tab-content'>
                          <table id="table-mytickets" class="table table-condensed table-hover" style='margin-bottom:0;' width="100%">
                            <tbody>
                            @foreach($offres as $key => $offre)
                                <tr>
                                    <td><strong>{{$key}}</strong></td>
                                    @if($offre == 'Non')
                                        <td> <strong><span class='label label-danger'>{{$offre}}</span></strong></td>
                                    @else
                                        <td> <strong><span class='label label-success'>{{$offre}}</span></strong></td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
