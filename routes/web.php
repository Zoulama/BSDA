<?php

Route::get('logout', function () {
    Saml::samlLogout();
})->middleware('auth');

//Route::group(['prefix' => '{slug}'], function() {

Route::get('/', 'HomeController@index');
    
Route::group(['prefix' => 'clients/{client_id}/prestations/bsod'], function () {
        Route::get('/add', array('as' => 'bsod.index', 'uses' => 'EligibilityController@index'));
        
        //Route::post('eligibility-liste', array('as' => 'bsod.eligibilityListe', 'uses' => 'EligibilityController@eligibilityMultiple'));
        Route::get('search_ville/autocomplete', 'EligibilityController@autocomplete');
        Route::get('search_voie/autocomplete', 'EligibilityController@autocompleteVoie');
});



Route::group(['prefix' => 'bsod'], function () {

    Route::post('eligibility', array('as' => 'bsod.eligibility', 'uses' => 'EligibilityController@eligibility'));

    Route::group(array('prefix' => 'appointments'), function() {
        Route::get('/', array('as' => 'Appointment.show', 'uses' => 'AppointmentController@showAppointement'));
        Route::get('prospect/{prospect_id}/{key}', array('as' => 'Appointment.prospect', 'uses' => 'AppointmentController@bookProspectAppointment'));
         Route::get('customer/{customerId}/{key}', array('as' => 'Appointment.customer', 'uses' => 'AppointmentController@bookCustomerAppointment'));
        Route::get('update/{key}/{new}', array('as' => 'Appointment.change', 'uses' => 'AppointmentController@changeAppointment'));
        Route::get('add/{id}/{client_id}/{typerdv}', array('as' => 'Appointment.index', 'uses' => 'AppointmentController@index'));
        Route::get('show/{id}/{typerdv}', array('as' => 'Appointment.detail', 'uses' => 'AppointmentController@detailProspect'));
        Route::get('edit/{id}/{typerdv}', array('as' => 'Appointment.edit', 'uses' => 'AppointmentController@editAppointment'));
        Route::post('getcalendar', array('as' => 'Appointment.getcalandar', 'uses' => 'AppointmentController@getCalendar'));

        
       
        Route::get('del/{key}/{typerdv}', array('as' => 'Appointment.delete', 'uses' => 'AppointmentController@deleteAppointement'));
        Route::get('datatable', array('as' => 'Appointment.datatable', 'uses' => 'AppointmentController@getDatatable'));
        Route::get('testdatatable', array('as' => 'Appointment.testdatatable', 'uses' => 'AppointmentController@dataTables'));
    });

    Route::group(array('prefix' => 'orders'), function() {
        Route::get('/', array('as' => 'Orders.index', 'uses' => 'CommandesController@index'));
        Route::get('create/{id}', array('as' => 'Orders.create', 'uses' => 'CommandesController@createCommande'));
        Route::get('abandon/{id}', array('as' => 'Orders.abandon', 'uses' => 'CommandesController@abandonOrder'));
        Route::post('commande', array('as' => 'Orders.send', 'uses' => 'CommandesController@send'));
        Route::post('create/add-option', array('as' => 'Option.add', 'uses' => 'CommandesController@addOption'));
        Route::get('edit/{client_id}/{id}', array('as' => 'Orders.edit', 'uses' => 'CommandesController@editOreder'));
        Route::post('edit/{client_id}/add-option', array('as' => 'Option.add', 'uses' => 'CommandesController@addOption'));
        Route::post('create/add-equipement', array('as' => 'Equipement.add', 'uses' => 'CommandesController@addEquipement'));
        Route::get('show/{client_id}/{id}', array('as' => 'Orders.detail', 'uses' => 'CommandesController@getDetailOreder'));
        Route::get('datatable', array('as' => 'Orders.datatable', 'uses' => 'CommandesController@getDatatable'));
    });

    Route::group(array('prefix' => 'clientbsod'), function() {
        Route::get('/', array('as' => 'ClientBsod.index', 'uses' => 'ClientBsodController@index'));
        Route::get('edit/{id}', array('as' => 'ClientBsod.edit', 'uses' => 'ClientBsodController@edit'));
        Route::get('show/{id}', array('as' => 'ClientBsod.detail', 'uses' => 'ClientBsodController@show'));
        Route::get('{id}/orders', array('as' => 'ClientBsod.listOrder', 'uses' => 'ClientBsodController@listeOrders'));
        Route::post('update', array('as' => 'ClientBsod.update', 'uses' => 'ClientBsodController@update'));
        Route::get('datatable', array('as' => 'ClientBsod.datatable', 'uses' => 'ClientBsodController@getDatatable'));
        Route::get('listOrderdatatable/{id}', array('as' => 'ClientBsod.listOrderDatatable', 'uses' => 'ClientBsodController@getDataListOrder'));
    });

    Route::group(array('prefix' => 'bsod-adress'), function() {
        Route::get('/', array('as' => 'BsodAdress.index', 'uses' => 'EligibilityController@eligibilityAdress'));
        Route::get('show/{id}', array('as' => 'BsodAdress.show', 'uses' => 'EligibilityController@show'));
        Route::get('datatable', array('as' => 'BsodAdress.datatable', 'uses' => 'EligibilityController@getDatatable'));
    });

    });

/*})->where(['slug' => '.*'])
->middleware('auth');*/
