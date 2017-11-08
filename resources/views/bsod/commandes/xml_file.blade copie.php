{!!$head!!}
<Message xmlns="http://completel.com/CommandeClientTHD/CommandeClient">
  <Commande>

    <En-tete>
      <Commande numCommande="{{$xmlData['Entete']['numCommande']}}" dateCommande="{{$xmlData['Entete']['dateCommande']}}" typeCommande="{{$xmlData['Entete']['typeCommande']}}"/>
      <ClientDemandeur codeClient="CODEPI-FIBRE"/>
      <ClientFacture codeClient="CODEPI-FIBRE"/>
      <ClientFinal codeClient="{{$xmlData['Entete']['codeClientFinal']}}" civilite="{{$xmlData['Entete']['civilite']}}" nom="{{$xmlData['Entete']['nom']}}" prenom="{{$xmlData['Entete']['prenom']}}" numContact="{{$xmlData['Entete']['numContact']}}" email="{{$xmlData['Entete']['email']}}" typeClient="{{$xmlData['Entete']['typeClient']}}"/>
      <Acces idAccesPrise="{{$xmlData['Entete']['idAccesPrise']}}" @if($xmlData['Entete']['typeCommande']!='C') identifiantClient="{{$xmlData['Entete']['identifiantClient']}}" @endif>
        <adresse numeroDansVoie="{{$xmlData['Entete']['numeroDansVoie']}}" libelleVoie="{{$xmlData['Entete']['libelleVoie']}}" codeINSEE="{{$xmlData['Entete']['codeINSEE']}}" codePostal="{{$xmlData['Entete']['codePostal']}}" commune="{{$xmlData['Entete']['commune']}}"/>
      @if($xmlData['Entete']['typeCommande']!='R')
      @if($xmlData['Entete']['typeCommande']=='C')
        <scheduleid>{{$xmlData['Entete']['scheduleid']}}</scheduleid>
      @endif
      @endif
      </Acces>
    </En-tete>
    @if($xmlData['Entete']['typeCommande']!='R' && $xmlData['Entete']['typeCommande']!='F')
    <Detail>
    @if(empty($xmlData['Echange']['tabEchange']))
      <Service>
       @if($xmlData['Entete']['typeCommande']=='C')
        @if(!empty($xmlData['Service']['fibre']))
        <FIBRE typeRaccordement="{{$xmlData['Service']['fibre']['typeRaccordement']}}" codeAction="{{$xmlData['Service']['fibre']['codeAction']}}">
          <CommentaireRaccordement>{{$xmlData['Service']['fibre']['CommentaireRaccordement']}}, Contact sur site : {{$xmlData['Entete']['contactSite']}}, DIGICODE : {{$xmlData['Entete']['digiCode']}}</CommentaireRaccordement>
        </FIBRE>
        @endif
        @endif
        @if(!empty($xmlData['Service']['data']))
        <Data codeAction="{{$xmlData['Service']['data']['codeAction']}}">
          <FluxData idFlux="1" codeAction="{{$xmlData['Service']['data']['codeAction']}}" typeRaccordement="FIBRE">
            <EquipementRef codeAction="{{$xmlData['Service']['data']['codeAction_EquRef']}}" numSequence="1"/>
            @if(!empty($xmlData['Service']['data']['option']))
              @foreach($xmlData['Service']['data']['option'] as $dataOption)
              <Option codeAction="{{$dataOption['code_action']}}" option="{{$dataOption['option']}}" valeur="{{$dataOption['valeur']}}"/>
              @endforeach
            @endif
          </FluxData>
        </Data>
        @endif
      </Service>
      @if(!empty($xmlData['Equipement']))
      <Equipement>
        <IAD numSequence="{{$xmlData['Equipement']['numSequence']}}" codeEAN13="{{$xmlData['Equipement']['codeEAN13']}}"  @if($xmlData['Entete']['typeCommande']=='C') numeroSerie="" @endif codeAction="{{$xmlData['Equipement']['codeAction_EquRef']}}" @if($xmlData['Entete']['typeCommande']=='C') codeActionOptionWifi="C" @endif>
        @if($xmlData['Entete']['typeCommande']=='C')
          <typeLivraison>{{$xmlData['Equipement']['typeLivraison']}}</typeLivraison>
        @endif
        </IAD>
      </Equipement>
      @endif
      @endif
    </Detail>
    @endif
    
  </Commande>
</Message>