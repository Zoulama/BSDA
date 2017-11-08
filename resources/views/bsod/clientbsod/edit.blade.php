@extends('layouts.template')

@section('page_css')

{!! HTML::style('assets/bower_resources/bootstrap/dist/css/bootstrap.min.css'); !!}

@stop

@section('page_header')
<i class='icon-edit'></i>
<span>{{trans('clientbsod.edit')}}</span>
@stop

@section('page_content')
    <div class='row-fluid'>
        <div class="tab-content">
            <div class="first block1 show" id="tab1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header blue-background">
                                <div class="title">{!!trans('orders.title_one')!!}</div>
                                </div><br>
                                <div class='row show'>
                                    <div class="form form-horizontal validate-form" style="margin-bottom: 0;">
                                      {!! Form::open(array('route' => 'ClientBsod.update', 'method' => 'post', 'id' => 'commande-form-ft', 'class' => 'form form-horizontal', 'style'=>'margin-bottom:0', 'data-parsley-validate' => 'true')) !!}
                                        @if(isset($client_bsod->id))
                                            <input type="hidden" id="bsod_client_id" name="bsod_client_id" value="{{$client_bsod->id}}"/>
                                        @endif
                                        @if(isset($client_bsod->id))
                                            <input type="hidden" id="client_adresse_id" name="client_adresse_id" value="{{$client_adresse->id}}"/>
                                        @endif
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="civilite"> {!!trans('orders.civilite')!!}</label>
                                            <div class="col-md-2 controls">
                                                <select id="first_name" name="first_name" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                @if(isset($client_bsod->gender))
                                                    <option value="{{$client_bsod->gender}}" selected="selected">{{$client_bsod->gender}}</option>
                                                @endif
                                                    <option value=""> {!!trans('orders.civilite')!!} -- </option>
                                                    <option value="M">M</option>
                                                    <option value="MME">MME</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="nom">{!!trans('orders.nom')!!}</label>
                                            <div class="col-md-2 controls">
                                            @if(isset($client_bsod->last_name))
                                                 {!! Form::text('nom',$client_bsod->last_name, array('id' => 'nom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.nom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @else
                                                {!! Form::text('nom','', array('id' => 'nom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.nom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="prenom">{!!trans('orders.prenom')!!}</label>
                                            <div class="col-md-2 controls">
                                            @if(isset($client_bsod->first_name))
                                                {!! Form::text('first_name',$client_bsod->first_name, array('id' => 'prenom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.prenom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @else
                                                 {!! Form::text('first_name','', array('id' => 'prenom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.prenom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @endif
                                            </div>
                                        </div>
                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="typeClient"> {!!trans('orders.typeClient')!!}</label>
                                            <div class="col-md-2 controls">
                                                <select id="typeClient" name="typeClient" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                @if(isset($client_bsod->client_type))
                                                    <option value="{{$client_bsod->client_type}}" selected="selected">{{$client_bsod->client_type}}</option>
                                                @endif
                                                            <option value="">{!!trans('orders.typeClient')!!} --- </option>
                                                            <option value="RESI">RESI</option>
                                                            <option value="PROF">PROF</option>
                                                            <option value="PRO">PRO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="numContact">{!!trans('orders.numContact')!!}</label>
                                            <div class="col-md-2 controls">
                                            @if(isset($client_bsod->telephone))
                                                  {!! Form::text('telephone',$client_bsod->telephone, array('id' => 'telephone' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                            @else
                                                  {!! Form::text('telephone','', array('id' => 'telephone' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                            @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="email">{!!trans('orders.email')!!}</label>
                                            <div class="col-md-2 controls">
                                            @if(isset($client_bsod->email))
                                                {!! Form::text('email',$client_bsod->email, array('id' => 'email' ,'required' => 'required','data-rule-digits' => 'true','class' =>' form-control','placeholder'=>trans('orders.email'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-type'=> 'email')) !!}
                                            @else
                                                {!! Form::text('email','', array('id' => 'email' ,'required' => 'required','data-rule-digits' => 'true','class' =>' form-control','placeholder'=>trans('orders.email'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-type'=> 'email')) !!}
                                            @endif
                                            </div>
                                        </div>
                                         <div class="form-group">
                                            <label class="col-md-3 control-label" for="numeroDansVoie">{!!trans('orders.numeroDansVoie')!!}</label>
                                            <div class="col-md-2 controls">
                                              @if(isset($client_adresse->street_number))
                                                {!! Form::text('street_number',$client_adresse->street_number, array('id' => 'street_number' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numeroDansVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                              @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="libelleVoie">{!!trans('orders.libelleVoie')!!}</label>
                                            <div class="col-md-2 controls">
                                              @if(isset($client_adresse->street))
                                                {!! Form::text('street',$client_adresse->street, array('id' => 'street' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.libelleVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                              @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="codePostal">{!!trans('orders.codePostal')!!}</label>
                                            <div class="col-md-2 controls">
                                              @if(isset($client_adresse->zipcode))
                                                {!! Form::text('zipcode',$client_adresse->zipcode, array('id' => 'zipcode' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.codePostal'),'data-parsley-group'=>'block1','data-parsley-type'=>'digits','maxlength'=>5)) !!}
                                              @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="commune">{!!trans('orders.commune')!!}</label>
                                            <div class="col-md-2 controls">
                                             @if(isset($client_adresse->city))
                                                {!! Form::text('city',$client_adresse->city, array('id' => 'city' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.commune'),'data-parsley-group'=>'block1') )!!}
                                             @endif
                                            </div>
                                        </div>
                                         </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class='form-actions pull-center' style='margin-bottom:0'>
                        <button class='btn btn-primary' type='submit'>
                          <i class='icon-save'></i>
                          Sauvegarder
                        </button>
                      </div>
                     {!! Form::close() !!}
            </div>
        </div>
    </div>
@stop

