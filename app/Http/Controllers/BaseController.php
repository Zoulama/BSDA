<?php namespace Provisioning\Http\Controllers;


class BaseController extends Controller {

    public function __construct()
    {
        $color_contrast = 'contrast-sea-blue';
        $color_body = 'light';
        $color_form = 'blue-background';
        $color_bandeau = 'green-background';

        view()->share('color_contrast', $color_contrast);
        view()->share('color_form', $color_form );
        view()->share('color_bandeau', $color_bandeau);
        view()->share('color_body', $color_body);
    }
}