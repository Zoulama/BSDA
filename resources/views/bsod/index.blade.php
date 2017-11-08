@extends('layouts.template')

@section('page_header')
<i class='icon-ok'></i>
<span>{!!trans('eligibilityadress.eligibility_test')!!}</span>
@stop

@section('page_content')
<div class='row'>
    <div class='col-sm-12 box' style='margin-bottom: 0'>
        <div class='box-header blue-background'>
           <div class='box-header {{ $color_form }}'>
                <div class='title'>{!!trans('bsod.eligibility.label.eligibilite_fibre')!!}</div>
            </div>
            <div class='actions'>
                <a class="btn box-remove btn-xs btn-link" href="#"><i class='icon-remove'></i>
                </a>

                <a class="btn box-collapse btn-xs btn-link" href="#"><i></i>
                </a>
            </div>
        </div>
        <div class='box-content'>
           @if(Session::has('message'))
                <div class="alert alert-danger alert-dismissable">
                    <a class="close" data-dismiss="alert" href="#">×</a>
                    <i class="icon-remove-sign"></i>
                    {{Session::get('message')}}
                </div>
            @endif
            <div class='tabbable' style='margin-top: 20px'>
                <ul class='nav nav-responsive nav-tabs'>
                    <li class='active'>
                        <a data-toggle='tab' href='#retab1'>
                            {!!trans('bsod.eligibility.label.renseigne_adresse')!!}
                        </a>
                    </li>
                </ul>
                <div class='tab-content'>
                    <div id="retab1" class="tab-pane active">
                        <div class='row-fluid'>
                            {!! Form::open(['route' => 'bsod.eligibility', 'method' => 'post', 'class' => 'form form-horizontal','id' => 'frm_eligibility', 'data-parsley-validate' => 'true']) !!}
                            <input type="hidden" name="clientId" value="{{$eligibilityParam['clientId']}}" id="clientId" class="form-control"/>
                            <div class='form-group'>
                                <label for="zipcode" class="col-md-3 control-label">{!!trans('bsod.eligibility.label.zipcode')!!}</label>
                                <div class='col-md-2 controls'>
                                    <input type="text" name="zipcode" value="{{$eligibilityParam['zipcode']}}" id="zipcode" class="form-control" data-parsley-required="true" placeholder="Ex: 93100" />
                                </div>
                            </div>

<!--                            <div id="voie_addr" style="display:none;">-->
                                    <div class='form-group'>
                                        <label for="street" class="col-md-3 control-label">{!!trans('bsod.eligibility.label.street')!!}</label>
                                        <div class='col-md-3 controls'>
                                            <input type="text" name="street" value="{{$eligibilityParam['street']}}" id="street" class="form-control" data-parsley-required="true" placeholder="Ex: rue de Paris" />
                                        </div>
                                    </div>
<!--                            </div>-->

<!--                             <div id="number_street" style="display:none;">-->
                                    <div class='form-group'>
                                        <label for="street_number" class="col-md-3 control-label">{!!trans('bsod.eligibility.label.street_number')!!}</label>
                                        <div class='col-md-3 controls'>
                                            <input type="text" name="street_number" value="{{$eligibilityParam['street_number']}}" class="form-control" />
                                        </div>
                                    </div>
 <!--                           </div>-->

                            <div class='form-actions form-actions-padding' style='margin-bottom:0'>
                                <div class="row text-right">
                                    <div class="col-md-9 col-md-offset-3">
                                        <button class='btn btn-primary {{ $color_form }} btn-lg' type='submit'>
                                            <i class="icon-large icon-check-sign"></i>
                                            {{Lang::get('button/button.update')}}
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
</div>
@stop
@section('page_js')
<script type="text/javascript">
    $(function()
    {
         $("#zipcode").autocomplete({
          source: "search_ville/autocomplete",
          minLength: 5,
          select: function(event, ui) {
            $('#zipcode').val(ui.item.value);
            $('#voie_addr').show();
          }
        });
    });

    $("#zipcode").change(function()
    {
        $('#voie_addr').show();
    });

    $(function() {
        $("#street").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "search_voie/autocomplete?addr="+ $("#zipcode").val(),
                    data: {
                        term: request.term,
                    },
                    success: function(data) {
                       $('#street').autocomplete({
                                source: data
                        });
                       $('#number_street').show();
                    }
                });
            },
            minLength:3,
        });
    });
</script>
@stop