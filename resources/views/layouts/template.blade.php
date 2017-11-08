<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{{Lang::get('template_lang.title')}}}</title>
    <link href="assets/stylesheets/app.css?e1edf4c497c8ffde6f3f" rel="stylesheet">
    @include('layouts.css_call')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    @yield('page_css')
</head>

<body class='contrast-sea-blue fixed-header <?php if(isset($_COOKIE["nav_stage"]) && $_COOKIE["nav_stage"]=="nav-close"){ echo 'main-nav-closed'; } else {echo 'main-nav-opened';} ?>'>
    @include('layouts.header')
    <div id='wrapper'>
        <div id='main-nav-bg'></div>
        @include('layouts.menu')
        <section id='content'>
            <div class='container'>
                <div class='row' id='content-wrapper'>
                    <div class='col-xs-12'>
                        <div class='row'>
                            <div class='col-xs-12'>
                                <div class='page-header'>
                                    <h1 class='pull-left'>
                                        @yield('page_header')
                                    </h1>
                                </div>
                            </div>
                        </div>
                        @yield('page_content')
                    </div>
                </div>
            </div>
        </section>
    </div>
    @include('layouts.js_call')
    {!! HTML::script('assets/bower_resources/bootbox/bootbox.js') !!}
    @yield('page_js')
</body>

</html>
 <head>