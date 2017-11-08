@extends('layouts.template')

@section('page_header')
<meta name="_token" content="{{ csrf_token() }}" />
<i class='icon-eye-open'></i>
<span>{!!trans('commande.order_info')!!}</span>
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
                            <i class='icon-user text-green'></i>
                             {!!trans('commande.client')!!}
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
                                    <th width="11%">{!!trans('commande.nom')!!}</th>
                                    <th width="23%">{!!trans('commande.prenom')!!}</th>
                                    <th width="36%">{!!trans('commande.civilite')!!}</th>
                                    <th width="16%">{!!trans('commande.typeClient')!!}</th>
                                    <th width="14">{!!trans('commande.numContact')!!}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                 <tr>
                                    <td>{{$client->last_name}}</td>
                                    <td>{{$client->first_name }}</td>
                                    <td>{{$client->gender}}</td>
                                    <td>{{$client->client_type}}</td>
                                    <td>{{$client->telephone}}</td>
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
   @if(isset($FIBRE) && !empty($FIBRE))
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-ticket'></i>
                             {!!trans('commande.service_fibre')!!}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="choix" id="choixModal" value="khoulio" />

                    <div class='box-content box-no-padding smalldiv'>
                        <div class='tab-content'>
                          <table id="table-new" class="table table-condensed table-hover" style='margin-bottom:0;' width="100%">
                            <tbody>
                                <tr>
                                    <td width="63%"><strong>{!!trans('commande.typeRaccordement')!!} :</strong></td>
                                    <td width="37%">{{$FIBRE[0]->FIBRE->typeRaccordement}} : <span class='label label-success'>{{$typeRacc[$FIBRE[0]->FIBRE->typeRaccordement]}}</span></td>
                                </tr>
                                <tr>
                                    <td width="37%"><strong>{!!trans('commande.CommentaireRaccordement')!!} :</strong></td>
                                    <td width="63%">{{$FIBRE[1]->CommentaireRaccordement}}</td>
                                </tr>
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if(isset($DATA) && !empty($DATA))
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-ticket'></i>
                             {!!trans('commande.service_data')!!}
                        </div>
                        <div class='actions'>
                            <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="choix" id="choixModal" value="khoulio" />

                    <div class='box-content box-no-padding smalldiv'>
                        <div class='tab-content'>
                          <table id="table-new" class="table table-condensed table-hover" style='margin-bottom:0;' width="100%">
                            <tbody>
                                @if(isset($DATA[1]->FluxData))
                                 <tr>
                                    <tr><td colspan="3"> <strong><center> <i class='icon-barcode'></i>  {!!trans('commande.flux_data')!!} :</center></strong></td></tr>
                                    <tr>
                                    <th width="50%"><strong>{!!trans('commande.codeAction')!!}</strong></th> 
                                        <td width="50%" colspan="2">{{$DATA[1]->FluxData->codeAction}} : <span class='label label-success'>{{$label_typeC[$DATA[1]->FluxData->codeAction]}}</span></td>
                                    </tr>
                                    <tr>
                                        <td width="50%"><strong>Raccordement :</strong></td>
                                        <td width="50%" colspan="2">{{$DATA[1]->FluxData->typeRaccordement}}</td>
                                    </tr>
                                </tr>
                                @endif
                                @if(isset($DATA[2]->options))
                                <tr>
                                    <tr><td colspan="3"> <strong><center><i class='icon-plus'></i>  {!!trans('commande.options')!!} :</center></strong></td></tr>
                                    <tr>
                                        <th width="10%">{!!trans('commande.codeAction')!!}</th>
                                        <th width="30%">{!!trans('commande.option')!!}</th>
                                        <th width="30%">{!!trans('commande.valeur')!!}</th>
                                    </tr>

                                    @foreach($DATA[2]->options as $opt)
                                    <tr>
                                        <td width="10%">{{$opt->codeAction}} : <span class='label label-success'>{{$label_typeC[$opt->codeAction]}}</span></td>
                                        <td width="30%">{{$opt->option}}</td>
                                        <td width="30%">{{$opt->valeur}}</td>
                                    </tr>
                                    @endforeach
                                </tr>
                                @endif
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class='col-sm-12 col-md-6'>
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-inbox text-warning'></i>
                             {!!trans('commande.order')!!}
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
                                <tr>
                                    <td><strong>{!!trans('commande.numCommande')!!}</strong></td>
                                    <td>{{$orders['numCommande']}}</td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.dateCommande')!!} </strong></td>
                                    <td>{{$orders['dateCommande']}}</td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.typeCommande')!!}</strong></td>
                                    <td>{{$orders['typeCommande']}} : <span class='label label-success'>{{$label_typeC[$orders['typeCommande']]}}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.id_prise')!!}</strong></td>
                                    <td>{{$client->accomodationId}}</td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.code_client_final')!!}</strong></td>
                                    <td>{{$client->externalSubscriberId}}</td>
                                </tr>

                                <tr>
                                    <td><strong>{!!trans('commande.client_identifiant')!!}</strong></td>
                                    <td>{{$client->customerId}}</td>
                                </tr>
                                 @if(!empty($orderType))
                                    <tr>
                                        <td><strong>{!!trans('commande.status')!!}</strong></td>
                                        <td>
                                            @if($orderType[$orders['typeCommande']]['status'] == 'NOK')
                                                <strong>
                                                    <span class='label label-danger'>{{$orderType[$orders['typeCommande']]['status']}}</span> :
                                                    <span class='label label-warning'>{{$orderType[$orders['typeCommande']]['msg']}}</span>
                                                 </strong>
                                            @else
                                                <strong><span class='label label-success'>{{$orderType[$orders['typeCommande']]['status']}}</span></strong>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                                @if(isset($terminedOrder['idService']))
                                <th width="50%"><strong>{!!trans('commande.idService')!!}</strong></th> 
                                        <td width="50%" colspan="2"><span class='label label-success'>{{$terminedOrder['idService']}}</span></td>
                                    </tr>
                                @endif
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(isset($DATA[1]->Equipement))
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-hdd text-red'></i>
                             {!!trans('commande.equipements')!!}
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
                                @if($orders['typeCommande'] === 'C')
                                <tr>
                                    <td><strong>{!!trans('commande.typeLivraison')!!}</strong></td>
                                    <td><strong><span class='label label-success'>{{$DATA[1]->Equipement->typeLivraison}}</span></strong></td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>{!!trans('commande.codeEAN13')!!} </strong></td>
                                    <td>
                                     @if(isset($DATA[1]->Equipement->codeEAN13))
                                        {{$DATA[1]->Equipement->codeEAN13}}
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.numeroSerie')!!} </strong></td>
                                    <td>{{$numeroSerie}}</td>
                                </tr>
                                <tr>
                                    <td><strong>{!!trans('commande.codeAction')!!}</strong></td>
                                    <td>
                                    @if(isset($DATA[1]->Equipement->codeAction))
                                        {{$DATA[1]->Equipement->codeAction}} : <span class='label label-success'>{{$label_typeC[$DATA[1]->Equipement->codeAction]}}</span>
                                    @endif
                                    </td>
                                </tr>
                            </tbody>
                          </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class='row'>
    <div class='col-sm-12 col-md-12'>
        <div class='row recent-activity'>
            <div class='col-sm-12'>
                <div class='box'>
                    <div class='box-header'>
                        <div class='title'>
                            <i class='icon-tasks text-warning'></i>
                             {!!trans('commande.ftp_server')!!}
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
                                    <th width="32%">{!!trans('commande.folder')!!}</th>
                                    <th width="22%">{!!trans('commande.arrived')!!}</th>
                                    <th width="10%">{!!trans('commande.file_status')!!}</th>
                                    <th width="25%">{!!trans('commande.msg')!!}</th>
                                    <th width="15%">{!!trans('commande.date_depot')!!}</th>
                                  </tr>
                                </thead>
                                <tbody>
                                @if(isset($ftp_follow['arcomIsArrived']) && !empty($ftp_follow['arcomIsArrived']))
                                    <tr>
                                        <td>{{$ftp_follow['arcomIsArrived']['arcom']}}</td>
                                        <td>{{$ftp_follow['arcomIsArrived']['recpetion'] }}</td>
                                        <td>{{$ftp_follow['arcomIsArrived']['status']}}</td>
                                        <td>{{$ftp_follow['arcomIsArrived']['msg']}}</td>
                                        <td>{{$ftp_follow['arcomIsArrived']['date']}}</td>
                                    </tr>
                                @endif

                                 @if(isset($ftp_follow['arFibreService']) && !empty($ftp_follow['arFibreService']))
                                    <tr>
                                        <td>
                                            @if(isset($ftp_follow['arFibreService'][$orders['numCommande']]['typeMessage']))
                                              {{ $ftp_follow['arFibreService'][$orders['numCommande']]['typeMessage'] }}
                                            @else
                                                {{ $ftp_follow['arFibreService']['typeMessage']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['arFibreService'][$orders['numCommande']]['typeAction']))
                                                {{ $ftp_follow['arFibreService'][$orders['numCommande']]['typeAction'] }}
                                            @else
                                                 {{ $ftp_follow['arFibreService']['typeAction'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['arFibreService'][$orders['numCommande']]['status']))
                                                {{$ftp_follow['arFibreService'][$orders['numCommande']]['status']}}
                                            @else
                                                {{$ftp_follow['arFibreService']['status']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['arFibreService'][$orders['numCommande']]['msg']))
                                                {{$ftp_follow['arFibreService'][$orders['numCommande']]['msg']}}
                                            @else
                                                {{$ftp_follow['arFibreService']['msg']}}
                                            @endif
                                        </td>
                                         <td>
                                            @if(isset($ftp_follow['arFibreService'][$orders['numCommande']]['date']))
                                                {{$ftp_follow['arFibreService'][$orders['numCommande']]['date']}}
                                            @else
                                                {{$ftp_follow['arFibreService']['date']}}
                                            @endif
                                         </td>
                                    </tr>
                                @endif

                                 @if(isset($ftp_follow['crFibreService']) && !empty($ftp_follow['crFibreService']))
                                    <tr>
                                        <td>{{$ftp_follow['crFibreService']['typeMessage']}}</td>
                                        <td>{{$ftp_follow['crFibreService']['typeAction']}}</td>
                                        <td>{{$ftp_follow['crFibreService']['status']}}</td>
                                        <td>{{$ftp_follow['crFibreService']['msg']}}</td>
                                         <td>{{$ftp_follow['crFibreService']['date']}}</td>
                                    </tr>
                                @endif

                                 @if(isset($ftp_follow['arCptl']) && !empty($ftp_follow['arCptl']))
                                    <tr>
                                        <td>
                                            @if(isset($ftp_follow['arCptl'][$orders['numCommande']]['typeMessage']))
                                                {{$ftp_follow['arCptl'][$orders['numCommande']]['typeMessage']}}
                                            @else
                                                {{$ftp_follow['arCptl']['typeMessage']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['arCptl'][$orders['numCommande']]['typeAction']))
                                                {{$ftp_follow['arCptl'][$orders['numCommande']]['typeAction'] }}
                                            @else
                                                {{$ftp_follow['arCptl']['typeAction'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['arCptl'][$orders['numCommande']]['status']))
                                                 {{$ftp_follow['arCptl'][$orders['numCommande']]['status']}}
                                            @else
                                                 {{$ftp_follow['arCptl']['status']}}
                                            @endif
                                       </td>
                                        <td>
                                            @if(isset($ftp_follow['arCptl'][$orders['numCommande']]['msg']))
                                                {{$ftp_follow['arCptl'][$orders['numCommande']]['msg']}}
                                            @else
                                                {{$ftp_follow['arCptl']['msg']}}
                                            @endif
                                        </td>
                                         <td>
                                            @if(isset($ftp_follow['arCptl'][$orders['numCommande']]['date']))
                                                {{$ftp_follow['arCptl'][$orders['numCommande']]['date']}}
                                            @else
                                                {{$ftp_follow['arCptl']['date']}}
                                            @endif
                                         </td>
                                    </tr>
                                @endif

                                @if(isset($ftp_follow['crCptl']) && !empty($ftp_follow['crCptl']))
                                    <tr>
                                        <td>
                                            @if(isset($ftp_follow['crCptl'][$orders['numCommande']]['typeMessage']))
                                                {{$ftp_follow['crCptl'][$orders['numCommande']]['typeMessage']}}
                                            @else
                                                {{$ftp_follow['crCptl']['typeMessage']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['crCptl'][$orders['numCommande']]['typeAction']))
                                                {{$ftp_follow['crCptl'][$orders['numCommande']]['typeAction'] }}
                                            @else
                                                {{$ftp_follow['crCptl']['typeAction'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['crCptl'][$orders['numCommande']]['status']))
                                                {{$ftp_follow['crCptl'][$orders['numCommande']]['status']}}
                                            @else
                                                {{$ftp_follow['crCptl']['status']}}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($ftp_follow['crCptl'][$orders['numCommande']]['msg']))
                                                {{$ftp_follow['crCptl'][$orders['numCommande']]['msg']}}
                                            @else
                                                {{$ftp_follow['crCptl']['msg']}}
                                            @endif
                                        </td>
                                         <td>
                                            @if(isset($ftp_follow['crCptl'][$orders['numCommande']]['date']))
                                                {{$ftp_follow['crCptl'][$orders['numCommande']]['date']}}
                                            @else
                                                {{$ftp_follow['crCptl']['date']}}
                                            @endif
                                         </td>
                                    </tr>
                                @endif
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
<script type="text/javascript">

</script>
@stop