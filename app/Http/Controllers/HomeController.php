<?php

namespace Provisioning\Http\Controllers;

use Illuminate\Http\Request;


class HomeController extends BaseController
{
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        return view('index');
    }
}
