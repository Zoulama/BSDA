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
    <!-- row-->
     <div class="row">
        <div class="col-md-12 box">
                            {!! Form::open(array('route' => 'commande.send', 'method' => 'post', 'id' => 'commande-form-ft', 'class' => 'form form-horizontal', 'style'=>'margin-bottom:0', 'data-parsley-validate' => 'true')) !!}
                            <input type="hidden" id="codeClient" name="codeClient" value=""/>
                            <input type="hidden" id="idAccesPrise" name="idAccesPrise" value="{{$datas->accomodationId}}"/>
                            <input type="hidden" id="identifiantClient" name="identifiantClient" value="{{$datas->externalSubscriberId}}"/>
                            <input type="hidden" id="codeINSEE" name="codeINSEE" value="{{$datas->code_insee}}"/>
                            <div class="tab-content">
                                <div class="first block1 show" id="tab1">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="box">
                                                <div class="box-header blue-background">
                                                    <div class="title">{!!trans('commande.title_one')!!}</div>
                                                    </div><br>
                                                    <div class='row show'>
                                                        <div class="form form-horizontal validate-form" style="margin-bottom: 0;">
                                                           <div class="form-group">
                                                                <label class="col-md-3 control-label" for="numCommande">{!!trans('commande.numCommande')!!}</label>
                                                                <div class="col-md-2 controls">
                                                                    {!! Form::text('numCommande','1254DDDE', array('id' => 'numCommande' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('Numero commande'))) !!}
                                                                </div>
                                                            </div>
                                                             <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="typeCommande">{!!trans('commande.typeCommande')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="typeCommande" name="typeCommande" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option value=""> Type commande -- </option>
                                                                            @if(isset($typeC))
                                                                                @foreach($typeC as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="civilite"> {!!trans('commande.civilite')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="civilite" name="civilite" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option value=""> Civilite -- </option>
                                                                            <option value="M">M</option>
                                                                            <option value="MME">MME</option>
                                                                        </select>
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="nom">{!!trans('commande.nom')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        {!! Form::text('nom','', array('id' => 'nom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('commande.nom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="prenom">{!!trans('commande.prenom')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                    {!! Form::text('prenom','', array('id' => 'prenom' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>trans('commande.prenom'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                                    </div>
                                                            </div>

                                                             <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="typeClient"> {!!trans('commande.typeClient')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="typeClient" name="typeClient" required data-parsley-group="block1" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option value=""> Type de client --- </option>
                                                                                    <option value="RESI">RESI</option>
                                                                                    <option value="PROF">PROF</option>
                                                                                    <option value="PRO">PRO</option>
                                                                        </select>
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="numContact">{!!trans('commande.numContact')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        {!! Form::text('numContact','', array('id' => 'numContact' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="email">{!!trans('commande.email')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                    {!! Form::text('email','', array('id' => 'email' ,'required' => 'required','data-rule-digits' => 'true','class' =>' form-control','placeholder'=>trans('commande.email'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-type'=> 'email')) !!}
                                                                    </div>
                                                            </div>

                                                             <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="numeroDansVoie">{!!trans('commande.numeroDansVoie')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                      @if(isset($datas->street_number))
                                                                        {!! Form::text('numeroDansVoie',$datas->street_number, array('id' => 'numeroDansVoie' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numeroDansVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                                      @endif
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="libelleVoie">{!!trans('commande.libelleVoie')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                      @if(isset($datas->street_number))
                                                                        {!! Form::text('libelleVoie',$datas->street_number_complement, array('id' => 'libelleVoie' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.libelleVoie'),'data-parsley-required' =>'true','data-parsley-group'=>'block1')) !!}
                                                                      @endif
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="codePostal">{!!trans('commande.codePostal')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                      @if(isset($datas->street_number))
                                                                        {!! Form::text('codePostal',$datas->zipcode, array('id' => 'codePostal' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.codePostal'),'data-parsley-group'=>'block1','data-parsley-type'=>'digits','maxlength'=>5)) !!}
                                                                      @endif
                                                                    </div>
                                                            </div>

                                                            <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="commune">{!!trans('commande.commune')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                     @if(isset($datas->town))
                                                                        {!! Form::text('commune',$datas->town, array('id' => 'commune' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.commune'),'data-parsley-group'=>'block1') )!!}
                                                                     @endif
                                                                    </div>
                                                            </div>
                                                             <div class="form-group">
                                                                <label class="col-md-3 control-label" for="numContact">{!!trans('commande.contact_sur_site')!!}</label>
                                                                <div class="col-md-2 controls">
                                                                    {!! Form::text('contact_sur_site','', array('id' => 'numContact' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numContact'),'data-parsley-required' =>'true','data-parsley-group'=>'block1','data-parsley-pattern'=>'^0[0-9]{9}$','data-parsley-type'=>'digits','maxlength'=>10)) !!}
                                                                </div>
                                                            </div>
                                                             <div class="form-group">
                                                                <label class="control-label col-md-3">{!!trans('commande.security_question')!!} </label> 
                                                                <div class="col-md-2 controls">
                                                                    <div class="radio">
                                                                        <label>
                                                                            <input type="radio" data-parsley-group="block1" name="SECUR_SITE" id="choice1" value="OUI">
                                                                            {!!trans('commande.yes')!!}
                                                                        </label>
                                                                    </div>
                                                                    <div class="radio">
                                                                        <label >
                                                                            <input type="radio" data-parsley-mincheck="1" required data-parsley-group="block1" name="SECUR_SITE" id="choice2" value="NON">
                                                                             {!!trans('commande.no')!!}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                             <div id="avec_ecure_code" style="display: none;">
                                                                <div class="form-group">
                                                                    <label class="control-label col-md-3" for="">{!!trans('commande.entry_code')!!}</label>
                                                                    <div class="col-md-2 controls">
                                                                        <input class="form-control" data-parsley-group="block2" maxlength='20' id="code_acces" name="code_acces" placeholder="Code d’entrée" type="text">
                                                                    </div>
                                                                </div>
                                                             </div>
                                                             <div class="form-group">
                                                                <label class="control-label col-md-3" for="">{!!trans('commande.comment_sur_site')!!}</label>
                                                                 <div class="col-md-2 controls">
                                                                    <textarea id="inputTextArea1" name="comment_sur_site" placeholder="Textarea" required data-parsley-group="block1" rows="4"></textarea>
                                                                  </div>
                                                               </div>
                                                             </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                        <span class="next btn btn-info pull-right" data-current-block="1" data-next-block="2">Next ></span>
                                    </div>
                                <div class="second block2 hidden" id="tab2">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="box">
                                                <div class="box-header orange-background">
                                                    <div class="title">{!!trans('commande.title_two')!!}</div>
                                                </div>
                                                <div class="box-content">
                                                    <div class="form form-horizontal validate-form" style="margin-bottom: 0;">
                                                        <hr class='hr-normal'>
                                                        <div class='form-group'>
                                                              <label class='col-md-3 control-label'>FIBRE</label>
                                                              <div class='col-md-2 controls'>
                                                                  <input type='checkbox' value='fibre' id="fibre" name="fibre"  onclick="showOption('fibre')">
                                                              </div>
                                                        </div>

                                                        <div id="fibre_hidden" style="display:none;">
                                                                <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="codeAction"> codeAction :</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="codeAction" name="codeAction_fibre[]"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> codeAction -- </option>
                                                                           @if(isset($Code_action))
                                                                                @foreach($Code_action as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label class="col-md-3 control-label" for="typeRaccordement"> Type raccordement :</label>
                                                                        <div class="col-md-2 controls">
                                                                            <select id="typeRaccordement" name="typeRaccordement_fibre"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> Type raccordement -- </option>
                                                                            @if(isset($type_racc))
                                                                                @foreach($type_racc as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                            </select>
                                                                        </div>
                                                                </div>

                                                                <div class='form-group'>
                                                                      <label class='col-md-3 control-label' for='CommentaireRaccordement'>Commentaire Raccordement</label>
                                                                      <div class='col-md-2 controls'>
                                                                        <textarea id='CommentaireRaccordement' name="CommentaireRaccordement" placeholder='Textarea' rows='3'></textarea>
                                                                      </div>
                                                                </div>
                                                        </div>

                                                        <hr class='hr-normal'>
                                                        <div class='form-group'>
                                                              <label class='col-md-3 control-label'>Data</label>
                                                              <div class='col-md-2 controls'>
                                                                <label class='checkbox'>
                                                                  <input type='checkbox' value='data' id="data" name="data" onclick="showOption('data')">
                                                                </label>
                                                              </div>
                                                        </div>

                                                        <div id="data_hidden" style="display:none;">
                                                               <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="codeAction"> codeAction :</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="codeAction" name="codeAction_data[]"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> codeAction -- </option>
                                                                             @if(isset($Code_action))
                                                                                @foreach($Code_action as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label class="col-md-3 control-label" for="typeRaccordement"> Type raccordement :</label>
                                                                        <div class="col-md-2 controls">
                                                                            <select id="typeRaccordement" name="typeRaccordement_data"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> Type raccordement -- </option>
                                                                            @if(isset($type_racc))
                                                                                @foreach($type_racc as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                            </select>
                                                                        </div>
                                                                </div>
                                                                <fieldset class="EquipementRef">
                                                                    <legend>EquipementRef</legend>
                                                                    <div class="form-group">
                                                                        <label class="col-md-3 control-label" for="numSequence">{!!trans('commande.numeroSerie')!!}</label>
                                                                        <div class="col-md-2 controls">
                                                                            {!! Form::text('numSequence_EquRef','1254DDDE', array('id' => 'numSequence' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numeroSerie'))) !!}
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label class="col-md-3 control-label" for="codeAction">Code action :</label>
                                                                        <div class="col-md-2 controls">
                                                                            <select id="codeAction_EquRef" name="codeAction_EquRef" class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected>---</option>
                                                                             @if(isset($Code_act))
                                                                                @foreach($Code_act as $value)
                                                                                    <option value="{{$value}}">{{$value}}</option>
                                                                                @endforeach
                                                                            @endif
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>

                                                                <fieldset class="Option">
                                                                <legend>Option</legend>
                                                                <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="codeAction">Code action :</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="codeAction_data_opt"   class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> Type raccordement -- </option>
                                                                            <option value="C">C</option>
                                                                            <option value="R">R</option>
                                                                            <option value="NA">NA</option>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="option">Option </label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="option_data_opt"   class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected>--</option>
                                                                            <option value="BOUQUET">BOUQUET</option>
                                                                            <option value="BASIC">BASIC</option>
                                                                            <option value="OPTION">OPTION</option>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="valeur">Valeur :</label>
                                                                    <div class="col-md-2 controls">
                                                                        {!! Form::text('valeur','', array('id' => 'valeur_data_opt' ,'required' => 'required','data-rule-digits' => 'true','class' => 'form-control','placeholder'=>'valeur')) !!}
                                                                    </div>
                                                                </div>

                                                            </fieldset>

                                                                <div class="pull-right">
                                                                    <button class="btn btn-primary  blue-background btn-lg" type="button"  id="data_opt">
                                                                        <i class="icon-plus"></i>
                                                                        {!!trans('commande.addoption')!!}
                                                                    </button>
                                                                </div><br><br>
                                                         </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="next btn btn-info pull-left" data-current-block="2" data-next-block="1">< Previous</span>
                                    <span class="next btn btn-info pull-right" data-current-block="2" data-next-block="3">Next ></span>
                                </div>

                                <div class="third block3 hidden" id="tab3">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="box">
                                                <div class="box-header red-background">
                                                    <div class="title">{!!trans('commande.title_three')!!}</div>
                                                </div>
                                                <div class="box-content">
                                                    <div class="form form-horizontal validate-form" style="margin-bottom: 0;">

                                                         <div class="form-group">
                                                                    <label class="col-md-3 control-label" for="codeAction">Code action :</label>
                                                                    <div class="col-md-2 controls">
                                                                        <select id="codeAction_equ" name="codeAction_equ"  class="form-control col-lg-12 col-md-12 col-sm-4 col-xs-12" type="text">
                                                                            <option  selected> Type raccordement -- </option>
                                                                            <option value="C">C</option>
                                                                            <option value="R">R</option>
                                                                            <option value="NA">NA</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                        <div class="form-group">
                                                                <label class="col-md-3 control-label" for="numSequence">{!!trans('commande.numSequence')!!}</label>
                                                                <div class="col-md-2 controls">
                                                                    {!! Form::text('numSequence','125445', array('id' => 'numSequence' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numSequence'))) !!}
                                                                </div>
                                                        </div>

                                                        <div class="form-group">
                                                                <label class="col-md-3 control-label" for="codeEAN13">{!!trans('commande.codeEAN13')!!}</label>
                                                                <div class="col-md-2 controls">
                                                                    {!! Form::text('codeEAN13','1254DDDE', array('id' => 'codeEAN13' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.codeEAN13'))) !!}
                                                                </div>
                                                        </div>

                                                        <div class="form-group">
                                                                <label class="col-md-3 control-label" for="numeroSerie">{!!trans('commande.numeroSerie')!!}</label>
                                                                <div class="col-md-2 controls">
                                                                    {!! Form::text('numeroSerie','1254DDDE', array('id' => 'numeroSerie' ,'required' => 'required','data-rule-digits' => 'true','class' => 'numbersOnly form-control','placeholder'=>trans('commande.numeroSerie'))) !!}
                                                                </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <span class="next btn btn-info pull-left" data-current-block="3" data-next-block="2">< Previous</span>
                                        <div class="pull-right">
                                            <button class="btn btn-primary  blue-background btn-lg" type="submit">
                                                <i class="icon-large icon-check-sign"></i>
                                                {!!trans('commande.commander')!!}
                                            </button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                            </div>
                        </div>
                </div>
          </div>
      </div>
    </div>
  <!-- row-->
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

                    $('.date-picker').focusout()//Added to remove focus from datepicker input box on selecting date
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

            // validation was ok. We can go on next step.
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
          } else {
              $('#'+id+'_hidden').hide();
          }
    }

    $(document).on('click',"input[name='SECUR_SITE']", function() {
            if ( $(this).attr('id') == "choice1") {
                $('#avec_ecure_code').show();
                $( "input[name*='code_acces']" ).prop('required',true);
            }
            else
            {
                $('#avec_ecure_code').hide();
                $('#code_acces').removeAttr('required');
            }
    });

    $(document).ready(function() {
        $('#data_opt').on('click', function (e) {
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
            var key= $('#identifiantClient').val();
            $.ajax({
                type:'post',
                url: 'add-option',
                data:{option_title,code_action,option,valeur,key},
                success: function(res) {
                    $('#codeAction_data_opt').val('');
                    $('#option_data_opt').val('');
                    $('#valeur_data_opt').val('');
                }
            });
        });
    });
</script>
@stop