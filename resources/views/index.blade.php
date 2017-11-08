@extends('layouts.template')

@section('page_css')
    {!! HTML::style('assets/stylesheets/style.min.css'); !!}
    {!! HTML::style('assets/stylesheets/evenement_style.css'); !!}
@stop

@section('page_header')
    <i class='icon-home'></i>
    <span>{!!trans('bsod.accueil')!!}</span>
@stop

@section('page_content')
    <div class='row box box-transparent'>
            <div class="col-xs-2">
                <div class='box-quick-link sea-blue-background'>
                    <a href='{{ URL::route("bsod.index")}}'>
                        <div class='header'>
                            <div class='icon-ok'></div>
                        </div>
                        <div class='content'>{!!trans('menu/menu_lang.eligibility')!!}</div>
                    </a>
                </div>
            </div>

            <div class="col-xs-2">
                <div class='box-quick-link green-background'>
                    <a href='{{ URL::route("Appointment.show")}}'>
                        <div class='header'>
                            <div class='icon-calendar'></div>
                        </div>
                        <div class='content'>{!!trans('menu/menu_lang.rdv')!!}</div>
                    </a>
                </div>
            </div>

            <div class="col-xs-2">
                <div class='box-quick-link orange-background'>
                    <a href='{{ URL::route("Orders.index")}}'>
                        <div class='header'>
                            <div class='icon-inbox'></div>
                        </div>
                        <div class='content'>{!!trans('menu/menu_lang.orders')!!}</div>
                    </a>
                </div>
            </div>

            <div class="col-xs-2">
                <div class='box-quick-link red-background'>
                    <a href='{{ URL::route("ClientBsod.index")}}'>
                        <div class='header'>
                            <div class='icon-user'></div>
                        </div>
                        <div class='content'>{!!trans('menu/menu_lang.bsod_client')!!}</div>
                    </a>
                </div>
            </div>

            <div class="col-xs-2">
                <div class='box-quick-link purple-background'>
                    <a href='{{ URL::route("BsodAdress.index")}}'>
                        <div class='header'>
                            <div class='icon-map-marker'></div>
                        </div>
                        <div class='content'>{!!trans('menu/menu_lang.eligibility_adres')!!}</div>
                    </a>
                </div>
            </div>
    </div>
@stop