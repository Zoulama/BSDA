@extends('layouts.template')

@section('page_header')
<meta name="_token" content="{{ csrf_token() }}" />
<i class='icon-inbox'></i>
<span>Prise de commande</span>
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
    @if(Session::has('error_message'))
       <div class="alert alert-danger">
          <button type="button" class="close" data-dismiss="danger">&times;</button>
          <strong> {{Session::get('error_message')}}</strong> <br>
        </div>
    @endif
    <!-- row-->
    <div class="row">
        <div class="col-md-12 box">
            {!! Form::open(array('route' => 'Orders.send', 'method' => 'post', 'id' => 'commande-form-ft', 'class' => 'form form-horizontal', 'style'=>'margin-bottom:0', 'data-parsley-validate' => 'true')) !!}
            <input type="hidden" id="idAccesPrise" name="idAccesPrise" value="{{$dataview['idAccesPrise']}}"/>
            <input type="hidden" id="codeClient" name="codeClient" value="{{$dataview['codeClient']}}"/>
            <input type="hidden" id="codeINSEE" name="codeINSEE" value="{{$dataview['codeINSEE']}}"/>
            @if(isset($dataview['scheduleid']))
            <input type="hidden" id="scheduleid" name="scheduleid" value="{{$dataview['scheduleid']}}"/>
             @endif
            <input type="hidden" id="appointment_id" name="appointment_id" value="{{$dataview['appointment_id']}}"/>

            @if(isset($dataview['eligibility_addresse_id']))
               <input type="hidden" id="eligibility_addresse_id" name="eligibility_addresse_id" value="{{$dataview['eligibility_addresse_id']}}"/>
            @endif

             @if(isset($dataview['bsod_client_id']))
               <input type="hidden" id="bsod_client_id" name="bsod_client_id" value="{{$dataview['bsod_client_id']}}"/>
            @endif
            

            @if(isset($dataview['order_id']))
               <input type="hidden" id="order_id" name="order_id" value="{{$dataview['order_id']}}"/>
            @endif

            @if(isset($dataview['client_id']))
               <input type="hidden" id="client_id" name="client_id" value="{{$dataview['client_id']}}"/>
            @endif
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
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="numCommande">{!!trans('orders.numCommande')!!}</label>
                                            <div class="col-md-2 controls">
                                                 @if(isset($dataview['numCommande']))
                                                    {!! Form::text('numCommande',$dataview['numCommande'], array('id' => 'numCommande' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('Numero commande'))) !!}
                                                @else
                                                {!! Form::text('numCommande',$dataview['last_inserId'], array('id' => 'numCommande' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('Numero commande'))) !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="typeCommande">{!!trans('orders.typeCommande')!!}</label>
                                            <div class="col-md-2 controls">
                                                <select id="typeCommande" name="typeCommande" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                    <option value=""> {!!trans('orders.typeCommande')!!} -- </option>
                                                    @if(isset($dataview['typeC']))
                                                        @foreach($dataview['typeC'] as $value)
                                                            <option value="{{$value}}">{{$value}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="civilite"> {!!trans('orders.civilite')!!}</label>
                                            <div class="col-md-2 controls">
                                                <select id="civilite" name="civilite" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
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
                                                {!! Form::text('nom',$dataview['last_name'], array('id' => 'nom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.nom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="prenom">{!!trans('orders.prenom')!!}</label>
                                            <div class="col-md-2 controls">
                                            @if(isset($client_bsod->first_name))
                                                {!! Form::text('prenom',$client_bsod->first_name, array('id' => 'prenom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.prenom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                            @else
                                                 {!! Form::text('prenom',$dataview['first_name'], array('id' => 'prenom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('orders.prenom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
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
                                                    {!! Form::text('numContact',$client_bsod->telephone, array('id' => 'numContact' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                                @else
                                                    {!! Form::text('numContact','', array('id' => 'numContact' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
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
                                                    {!! Form::text('numeroDansVoie',$client_adresse->street_number, array('id' => 'numeroDansVoie' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numeroDansVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="libelleVoie">{!!trans('orders.libelleVoie')!!}</label>
                                            <div class="col-md-2 controls">
                                                @if(isset($client_adresse->street))
                                                    {!! Form::text('libelleVoie',$client_adresse->street, array('id' => 'libelleVoie' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.libelleVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="codePostal">{!!trans('orders.codePostal')!!}</label>
                                            <div class="col-md-2 controls">
                                                @if(isset($client_adresse->zipcode))
                                                    {!! Form::text('codePostal',$client_adresse->zipcode, array('id' => 'codePostal' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.codePostal'),'data-parsley-group'=>'block1','data-parsley-type'=>'digits','maxlength'=>5)) !!}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-md-3 control-label" for="commune">{!!trans('orders.commune')!!}</label>
                                            <div class="col-md-2 controls">
                                                @if(isset($client_adresse->city))
                                                    {!! Form::text('commune',$client_adresse->city, array('id' => 'commune' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.commune'),'data-parsley-group'=>'block1') )!!}
                                                @endif
                                            </div>
                                        </div>
                                        <div id="contact_sur_site">
                                             <div class="form-group">
                                                <label class="col-md-3 control-label" for="numContact">{!!trans('orders.contact_sur_site')!!}</label>
                                                <div class="col-md-2 controls">
                                                    {!! Form::text('contact_sur_site','', array('id' => 'numContact' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-3">{!!trans('orders.security_question')!!} </label> 
                                            <div class="col-md-2 controls">
                                                <div class="radio">
                                                    <label>
                                                        <input type="radio" data-parsley-group="block1" name="SECUR_SITE" id="choice1" value="OUI">
                                                        {!!trans('orders.yes')!!}
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label >
                                                        <input type="radio" data-parsley-mincheck="1" required data-parsley-group="block1" name="SECUR_SITE" id="choice2" value="NON">
                                                         {!!trans('orders.no')!!}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="avec_ecure_code" style="display: none;">
                                            <div class="form-group">
                                                <label class="control-label col-md-3" for="">{!!trans('orders.entry_code')!!}</label>
                                                <div class="col-md-2 controls">
                                                    <input class="form-control" data-parsley-group="block1" maxlength='20' id="code_acces" name="code_acces" placeholder="Code d’entrée" type="text">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="next btn btn-info pull-right" data-current-block="1" data-next-block="2">Next></span>
                </div>
                <div class="second block2 hidden" id="tab2">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-header orange-background">
                                    <div class="title">{!!trans('orders.title_two')!!}</div>
                                </div>
                                <div class="box-content">
                                    <div class="form form-horizontal validate-form" style="margin-bottom: 0;">
                                        <hr class='hr-normal'>
                                        <div class='form-group'>
                                              <label class='col-md-3 control-label'>{!!trans('orders.fibre')!!}</label>
                                              <div class='col-md-2 controls'>
                                                  <input type='checkbox' value='fibre' id="fibre"  data-parsley-group="block2" name="fibre"  onclick="showOption('fibre')">
                                              </div>
                                        </div>
                                        <div id="fibre_hidden" style="display:none;">
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="codeAction">{!!trans('orders.codeAction')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                <div class="col-md-2 controls">
                                                    <select id="codeAction_fibre" name="codeAction_fibre[]" data-parsley-group="block2" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                        <option value=""> {!!trans('orders.codeAction')!!} -- </option>
                                                       @if(isset($dataview['Code_action']))
                                                            @foreach($dataview['Code_action'] as $Code_action)
                                                                <option value="{{$Code_action}}">{{$Code_action}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-md-3 control-label" for="typeRaccordement">
                                                    {!!trans('orders.typeRaccordement')!!} {!!trans('orders.deux_point_sign')!!}
                                                </label>
                                                <div class="col-md-2 controls">
                                                    <select id="typeRaccordement_fibre" name="typeRaccordement_fibre" data-parsley-group="block2"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="control-label col-md-3">{!!trans('orders.CommentaireRaccordement')!!} {!!trans('orders.deux_point_sign')!!}</label><br>
                                                <div class="col-md-4 controls">
                                                    <textarea class="form-control" data-parsley-group="block3" maxlength='200' id="CommentaireRaccordement" data-parsley-group="block2" name="CommentaireRaccordement" placeholder="Commentaire de raccordement" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class='hr-normal'>
                                        <div class='form-group'>
                                            <label class='col-md-3 control-label'>{!!trans('orders.data')!!}</label>
                                            <div class='col-md-2 controls'>
                                                <label class='checkbox'>
                                                  <input type='checkbox' value='data' id="data"  required data-parsley-group="block2" name="data" onclick="showOption('data')">
                                                </label>
                                            </div>
                                        </div>
                                        <div id="data_hidden" style="display:none;">
                                           <div class="form-group">
                                                <label class="col-md-3 control-label" for="codeAction">{!!trans('orders.codeAction')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                <div class="col-md-2 controls">
                                                    <select id="codeAction_data" name="codeAction_data[]" data-parsley-group="block2"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                        <option value=""> {!!trans('orders.codeAction')!!} -- </option>
                                                         @if(isset($dataview['Code_action']))
                                                            @foreach($dataview['Code_action'] as $Code_action)
                                                                <option value="{{$Code_action}}">{{$Code_action}}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <fieldset class="EquipementRef">
                                                <legend>EquipementRef</legend>

                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="codeAction">
                                                        {!!trans('orders.typeEquipement')!!} {!!trans('orders.deux_point_sign')!!}
                                                    </label>
                                                    <div class="col-md-2 controls">
                                                        <select id="type_EquRef" name="type_EquRef"  data-parsley-group="block2" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                        <option value="">---</option>
                                                        <option value="IAD">IAD</option>
                                                        <option value="DD">DD</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="typeLivraison_div">
                                                    <div class="form-group">
                                                        <label class="col-md-3 control-label" for="typeLivraison">
                                                            {!!trans('orders.typeLivraison')!!} {!!trans('orders.deux_point_sign')!!}
                                                        </label>
                                                        <div class="col-md-2 controls">
                                                            <select id="typeLivraison" name="typeLivraison"  data-parsley-group="block3"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                            <option value=""> {!!trans('orders.typeLivraison')!!} -- </option>
                                                            @if(isset($dataview['type_liv']))
                                                                @foreach($dataview['type_liv'] as $value)
                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                @endforeach
                                                            @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="numSequence">{!!trans('orders.numSequence')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                        {!! Form::text('numSequence_EquRef','1', array('id' => 'numSequence_EquRef' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numSequence'))) !!}
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="codeAction">
                                                        {!!trans('orders.codeAction')!!} {!!trans('orders.deux_point_sign')!!}
                                                    </label>
                                                    <div class="col-md-2 controls">
                                                        <select id="codeAction_EquRef" name="codeAction_EquRef"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                        <option value="">---</option>
                                                         @if(isset($dataview['codeAction_EquRef']))
                                                            @foreach($dataview['codeAction_EquRef'] as $value)
                                                                <option value="{{$value}}">{{$value}}</option>
                                                            @endforeach
                                                        @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="codeEAN13">{!!trans('orders.codeEAN13')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                    @if(isset($dataEquip['codeEAN13']))
                                                        {!! Form::text('codeEAN13',$dataEquip['codeEAN13'], array('id' => 'codeEAN13_EquRef' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.codeEAN13'))) !!}
                                                    @else
                                                        {!! Form::text('codeEAN13','3425160331516', array('id' => 'codeEAN13_EquRef' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.codeEAN13'))) !!}
                                                    @endif
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="numeroSerie">{!!trans('orders.numeroSerie')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                     @if(isset($dataEquip['numeroSerie']))
                                                         {!! Form::text('numeroSerie',$dataEquip['numeroSerie'], array('id' => 'numeroSerie_EquRef','required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numeroSerie'))) !!}
                                                    @else
                                                        {!! Form::text('numeroSerie','', array('id' => 'numeroSerie_EquRef','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('orders.numeroSerie'))) !!}
                                                     @endif
                                                    </div>
                                                </div>
                                            </fieldset>
                                            <fieldset class="Option">
                                                <legend >Option</legend>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="codeAction">{!!trans('orders.codeAction')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                        <select id="codeAction_data_opt"   class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                            <option value=""> {!!trans('orders.typeRaccordement')!!}-- </option>
                                                            <option value="C">C</option>
                                                            <option value="R">R</option>
                                                            <option value="NA">NA</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="option">{!!trans('orders.options')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                        <select id="option_data_opt"   class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                            <option value="">--</option>
                                                            <option value="BASIC">BASIC</option>
                                                            <option value="OPTION">OPTION</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="col-md-3 control-label" for="valeur">{!!trans('orders.valeur')!!} {!!trans('orders.deux_point_sign')!!}</label>
                                                    <div class="col-md-2 controls">
                                                         <select id="valeur_data_opt" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                   <div class="col-md-2 controls">
                                                        <button class="btn btn-primary  blue-background btn-lg" type="button"  id="data_opt">
                                                            <i class="icon-plus"></i>
                                                            {!!trans('orders.addoption')!!}
                                                        </button>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="next btn btn-info pull-left" data-current-block="2" data-next-block="1">< Previous</span>
                    <div class="pull-right">
                            <button class="btn btn-primary  blue-background btn-lg" type="submit">
                                <i class="icon-large icon-check-sign"></i>
                                {!!trans('orders.commander')!!}
                            </button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div> <!-- row-->
</div> 
@stop
@section('page_js')
<script type="text/javascript">
    $(function() {
        $('.date-picker').datepicker(
        {
            dateFormat: "yy-mm-dd",
            timeFormat: "hh:mm:ss",
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

    $(document).ready(function() {
        $('.next').on('click', function () {
            var current = $(this).data('currentBlock'),
                    next = $(this).data('nextBlock');

            if (next > current)
                if (false === $('#commande-form-ft').parsley().validate('block' + current))
                    return;

            $('.block' + current)
                    .removeClass('show')
                    .addClass('hidden');

            $('.block' + next)
                    .removeClass('hidden')
                    .addClass('show');
        });
    });

    function showOption(id) {
        if($('#'+id).is(":checked")) {
          $('#'+id+'_hidden').show();
          $("#codeAction_"+id).attr('required', 'required');
          $("#typeRaccordement_"+id).attr('required', 'required');

            switch (id) {
                case 'data':
                        $("#codeAction_EquRef").attr('required', 'required');
                        $("#type_EquRef").attr('required', 'required');
                    break;
                case 'fibre':
                        $("#CommentaireRaccordement").attr('required', 'required');
                    break;
                case 'echange':
                        $("#newNumeroSerie").attr('required', 'required');
                        $("#newCodeEAN13").attr('required', 'required');
                        $('#data').removeAttr('required');
                    break;
            }
        } else{
          $('#'+id+'_hidden').hide();
            $("#codeAction_"+id).removeAttr('required');
            $("#typeRaccordement_"+id).removeAttr('required');
            $("#newNumeroSerie").removeAttr('required');
            $("#newCodeEAN13").removeAttr('required');
            if (id == 'data') {
             $("#codeAction_EquRef").removeAttr('required');
          }
        }
    }

    $(document).on('click',"input[name='SECUR_SITE']", function() {
        if ( $(this).attr('id') == "choice1") {
            $('#avec_ecure_code').show();
            $("input[name*='code_acces']").prop('required',true);
        }
        else
        {
            $('#avec_ecure_code').hide();
            $('#code_acces').removeAttr('required');
        }
    });

    $(document).ready(function() {
        $('#data_opt').on('click', function (e) {

            if($('#valeur_data_opt').val()==="" || $('#codeAction_data_opt').val()===""  || $('#option_data_opt').val()==="")
            {
                bootbox.dialog({
                    message: "Veuillez remplir les champs",
                    title: "Champs options",
                    buttons: {
                        success: {
                            label: "D\'accord!",
                            className: "btn-success",
                            callback: function () {
                            }
                        }
                    }
                });
            }else{
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                e.preventDefault();
                var option_title = 'data_opt';
                var code_action = $('#codeAction_data_opt').val();
                var option = $('#option_data_opt').val();
                var valeur = $('#valeur_data_opt').val();
                var key = $('#codeClient').val();
                $.ajax({
                    type:'post',
                    url: 'add-option',
                    data:{option_title,code_action,option,valeur,key},
                    success: function(res) {
                        $('#codeAction_data_opt').val('-----');
                        $('#option_data_opt').val('--------');
                        $('#valeur_data_opt').val('');
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        $('#data_equip').on('click', function (e) {
            if($('#type_EquRef').val()==="" || $('#numeroSerie_EquRef').val()===""  || $('#codeEAN13_EquRef').val()==="" || $('#codeAction_EquRef').val()==="")
            {
                bootbox.dialog({
                    message: "Veuillez remplir les champs",
                    title: "Champs reference equipement",
                    buttons: {
                        success: {
                            label: "D\'accord!",
                            className: "btn-success",
                            callback: function () {
                            }
                        }
                    }
                });
            }else{
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                })
                e.preventDefault();
                var option_title = 'data_equip';
                var code_action = $('#codeAction_EquRef').val();
                var typequip = $('#type_EquRef').val();
                var num_serie = $('#numeroSerie_EquRef').val();
                var code_ean13 = $('#codeEAN13_EquRef').val();
                var num_sequence = $('#numSequence_EquRef').val();
                var key = $('#identifiantClient').val();
                $.ajax({
                    type:'post',
                    url: 'add-equipement',
                    data:{option_title,num_sequence,code_action,typequip,num_serie,code_ean13,key},
                    success: function(res) {
                        $('#type_EquRef').val('-----');
                        $('#numeroSerie_EquRef').val('');
                        $('#codeEAN13_EquRef').val('');
                        $('#codeAction_EquRef').val('');
                        $('#numSequence_EquRef').val()= $('#numSequence_EquRef').val() + 1;
                    }
                });
            }
        });
    });

    $(document).ready(function() {
        $('#option_data_opt').on('change', function()
        {
            $('#valeur_data_opt').empty();
             $('#valeur_data_opt').append('<option value="">selectionez une valeur</option>');
                var BASIC =  {!! json_encode($bsod_offers['basic']) !!};
                var OPTION = {!! json_encode($bsod_offers['option']) !!};
            if (this.value =='OPTION')
            {
                jQuery.each( OPTION, function( i, val ) {
                  $('#valeur_data_opt').append($('<option>',
                     {
                        value: val,
                        text : val
                    }));
                });
            }else{
                 jQuery.each(BASIC, function( i, val ) {
                  $('#valeur_data_opt').append($('<option>',
                     {
                        value: val,
                        text : val
                    }));
                });
            }
        });
    });

    $(document).ready(function() {
        $('#typeCommande').on('change', function()
        {
            var typeRacorrdement = [];
            var Code_act = [];
             if (this.value !='C') {
                $('#contact_sur_site').hide();
                $('#numContact').removeAttr('required');
                $('#typeLivraison_div').hide();
                $('#typeLivraison').removeAttr('required');
                if (this.value !='R') {
                    $("#numeroSerie").attr('required', 'required');
                }
            }
             $("#data").attr('required', 'required');
             $('#typeRaccordement_fibre').empty();

            switch (this.value) {
                case 'C':
                    typeRacorrdement = ['RE','REAC','REACI'];
                        jQuery.each( typeRacorrdement, function( i, val ) {
                            $('#typeRaccordement_fibre').append($('<option>',
                            {
                                value: val,
                                text : val
                            }));
                        });
                    break;
                case 'M':
                    typeRacorrdement = ['INST','ACI'];
                        jQuery.each( typeRacorrdement, function( i, val ) {
                            $('#typeRaccordement_fibre').append($('<option>',
                            {
                                value: val,
                                text : val
                            }));
                        });
                    break;
                case 'R':
                       $('#data').removeAttr('required');
                    break;
                case 'D':
                    break;
            }
        });
    });
</script>
@stop