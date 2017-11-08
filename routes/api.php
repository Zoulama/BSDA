<?php

Route::group(['prefix' => 'v1', 'middleware' => 'auth'], function () {

    Route::get('/me', function () {
        return response()->json(['data' => [
            'firstname' => AuthUser::getUserAttribute('firstname'),
            'lastname' => AuthUser::getUserAttribute('lastname'),
            'applications' => AuthUser::getApplications(),
            'url_profile' => AuthUser::getUrlProfile(),
        ]]);
    });

    Route::group(['prefix' => 'clients/{client}', 'middleware' => 'canAccessClient'], function () {

        Route::get('/', 'ClientController@show');
        Route::get('/prestations', 'ClientController@getClientsPrestations');
        Route::post('/prestations/centrex', 'CentileEnterpriseController@store');
        Route::post('/prestations/trunk', 'CentileTrunkController@store');
    });

    Route::get('/prestations/{prestation}', 'PrestationController@show')
        ->middleware('canAccessPrestation');

    Route::group(['prefix' => 'centile'], function () {

        Route::group(['prefix' => 'trunk'], function () {
            Route::group(['prefix' => '/{prestation}', 'middleware' => 'canAccessPrestation'], function () {

                Route::get('/call-barrings', 'CentileCallBarringController@index');

                Route::get('/', 'CentileTrunkController@show');
                Route::patch('/', 'CentileTrunkController@update');
                Route::delete('/', 'CentileTrunkController@destroy');

                Route::group(['prefix' => '/pstns'], function () {

                    Route::get('/', 'CentileTrunkPstnNumberController@index');
                    Route::post('/', 'CentileTrunkPstnNumberController@store');

                    Route::group(['prefix' => '/{pstn}'], function () {

                        Route::get('/', 'CentileTrunkPstnNumberController@show');
                        Route::delete('/', 'CentileTrunkPstnNumberController@destroy');
                    });
                });

            });
        });

        Route::group(['prefix' => 'centrex'], function () {

            Route::get('/dial-plan/reserved', 'CentileDialPlanController@reserved');

            Route::group(['prefix' => '/{prestation}', 'middleware' => 'canAccessPrestation'], function () {

                Route::get('/call-barrings', 'CentileCallBarringController@index');

                Route::get('/', 'CentileEnterpriseController@show');
                Route::patch('/', 'CentileEnterpriseController@update');
                Route::delete('/', 'CentileEnterpriseController@destroy');

                Route::group(['prefix' => '/pstns'], function () {

                    Route::get('/', 'CentilePSTNNumberController@index');
                    Route::post('/', 'CentilePSTNNumberController@store');

                    Route::group(['prefix' => '/{pstn}'], function () {

                        Route::get('/', 'CentilePSTNNumberController@show');
                        Route::delete('/', 'CentilePSTNNumberController@destroy');
                        Route::patch('/', 'CentilePSTNNumberController@update');

                    });
                });

                Route::group(['prefix' => '/device-models'], function () {
                    Route::get('/', 'CentileDeviceModelController@index');
                    Route::get('/{name}', 'CentileDeviceModelController@show');
                });

                Route::group(['prefix' => '/devices'], function () {

                    Route::get('/', 'CentileDeviceController@index');
                    Route::post('/', 'CentileDeviceController@store');

                    Route::group(['prefix' => '/{device}'], function () {

                        Route::get('/', 'CentileDeviceController@show');
                        Route::patch('/', 'CentileDeviceController@update');
                        Route::delete('/', 'CentileDeviceController@destroy');

                        Route::group(['prefix' => '/terminals'], function () {

                            Route::get('/{terminal}', 'CentileTerminalController@show');
                            Route::patch('/{terminal}', 'CentileTerminalController@update');
                            Route::delete('/{terminal}', 'CentileTerminalController@destroy');
                        });
                        Route::group(['prefix' => '/lines'], function () {

                            Route::get('/', 'CentileLineController@index');
                            Route::post('/', 'CentileLineController@store');
                            Route::patch('/{line}', 'CentileLineController@update');
                            Route::delete('{line}', 'CentileLineController@destroy');
                        });
                    });
                });

                Route::group(['prefix' => '/logical-terminals'], function () {

                    Route::get('/', 'CentileLogicalTerminalController@index');
                    Route::post('/', 'CentileLogicalTerminalController@store');
                    Route::get('/{logicalTerminal}', 'CentileLogicalTerminalController@show');
                    Route::patch('/{logicalTerminal}', 'CentileLogicalTerminalController@update');
                    Route::delete('/{logicalTerminal}', 'CentileLogicalTerminalController@destroy');
                });

                Route::group(['prefix' => '/users'], function () {

                    Route::get('/', 'CentileUserController@index');
                    Route::post('/', 'CentileUserController@store');

                    Route::group(['prefix' => '/{user}'], function () {

                        Route::get('/', 'CentileUserController@show');
                        Route::patch('/', 'CentileUserController@update');
                        Route::delete('/', 'CentileUserController@destroy');
                    });
                });

                Route::group(['prefix' => '/user-extensions'], function () {

                    Route::get('/all', 'CentileUserExtensionController@all');
                    Route::get('/assigned-to-user', 'CentileUserExtensionController@assignedToUser');
                    Route::get('/not-assigned-to-user', 'CentileUserExtensionController@notAssignedToUser');
                });

                Route::group(['prefix' => 'forwardings'], function () {

                    Route::get('/', 'CentileForwardingController@index');
                    Route::post('/', 'CentileForwardingController@store');
                    Route::get('/{forwarding}', 'CentileForwardingController@show');
                    Route::patch('/{forwarding}', 'CentileForwardingController@update');
                    Route::delete('/{forwarding}', 'CentileForwardingController@destroy');
                });

                Route::group(['prefix' => '/extensions-groups'], function () {

                    Route::get('/', 'CentileExtensionsGroupController@index');
                    Route::post('/', 'CentileExtensionsGroupController@store');
                    Route::get('/{extensionsGroup}', 'CentileExtensionsGroupController@show');
                    Route::patch('/{extensionsGroup}', 'CentileExtensionsGroupController@update');
                    Route::delete('/{extensionsGroup}', 'CentileExtensionsGroupController@destroy');
                });

                Route::group(['prefix' => '/speed-dials'], function () {

                    Route::get('/', 'CentileSpeedDialController@index');
                    Route::post('/', 'CentileSpeedDialController@store');
                    Route::get('/{speedDial}', 'CentileSpeedDialController@show');
                    Route::patch('/{speedDial}', 'CentileSpeedDialController@update');
                    Route::delete('/{speedDial}', 'CentileSpeedDialController@destroy');
                });

                Route::group(['prefix' => '/extensions'], function () {

                    Route::get('/assigned', 'CentileExtensionController@assigned');
                    Route::get('/unassigned', 'CentileExtensionController@unassigned');
                    Route::get('/not-assigned-to-user', 'CentileExtensionController@notAssignedToUser');
                    Route::get('/monitoring-buttons-autocomplete', 'CentileExtensionController@monitoring');
                });

                Route::group(['prefix' => '/voicemail'], function () {

                    Route::post('/', 'CentileVoicemailController@store');
                    Route::patch('/', 'CentileVoicemailController@update');
                    Route::delete('/', 'CentileVoicemailController@destroy');
                });

                Route::group(['prefix' => '/conference'], function () {

                    Route::post('/', 'CentileConferenceBridgeController@store');
                    Route::patch('/', 'CentileConferenceBridgeController@update');
                    Route::delete('/', 'CentileConferenceBridgeController@destroy');
                });

                Route::group(['prefix' => '/callqueuing'], function () {

                    Route::post('/', 'CentileCallQueuingController@store');
                    Route::delete('/', 'CentileCallQueuingController@destroy');
                });

                Route::get('/services', 'CentileServicesController@index');

            });
        });
    });
});
