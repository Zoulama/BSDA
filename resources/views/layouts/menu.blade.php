<nav id='main-nav'>
    <div class='navigation'>
        <ul class='nav nav-stacked'>
            <li  @if(Request::is('bsod/') || Request::is('bsod/*') ) class="active" @endif>
                <a class="dropdown-collapse" href="#"><i class="icon-signal"></i>
                    <span>{!!trans('menu/menu_lang.bsod')!!}</span>
                    <i class="icon-angle-down angle-down"></i>
                </a>


                 <ul @if(Request::is('bsod') || Request::is('bsod/*') || Request::is('bsod/appointments') || Request::is('bsod/appointments/*')) class="in nav nav-stacked" @else class="nav nav-stacked" @endif>
                    <li @if(Request::is('bsod/appointments') || Request::is('bsod/appointments/*')) class="active" @endif>
                        <a href="{{ URL::route('Appointment.show')}}" >
                            <i class="icon-calendar"></i>
                            <span>{!!trans('menu/menu_lang.rdv')!!}</span>
                        </a>
                    </li>
                </ul>

                <ul @if(Request::is('bsod') || Request::is('bsod/*') || Request::is('bsod/orders') || Request::is('bsod/orders/*')) class="in nav nav-stacked" @else class="nav nav-stacked" @endif>
                    <li @if(Request::is('bsod/orders') || Request::is('bsod/orders/*')) class="active" @endif>
                        <a href="{{ URL::route('Orders.index')}}" >
                            <i class="icon-inbox"></i>
                            <span>{!!trans('menu/menu_lang.orders')!!}</span>
                        </a>
                    </li>
                </ul>

                <ul @if(Request::is('bsod') || Request::is('bsod/*') || Request::is('bsod/clientbsod') || Request::is('bsod/clientbsod/*')) class="in nav nav-stacked" @else class="nav nav-stacked" @endif>
                    <li @if(Request::is('bsod/clientbsod') || Request::is('bsod/clientbsod/*')) class="active" @endif>
                        <a href="{{ URL::route('ClientBsod.index')}}" >
                            <i class="icon-user"></i>
                            <span>{!!trans('menu/menu_lang.bsod_client')!!}</span>
                        </a>
                    </li>
                </ul>

                <ul @if(Request::is('bsod') || Request::is('bsod/*') || Request::is('bsod/bsod-adress') || Request::is('bsod/bsod-adress/*')) class="in nav nav-stacked" @else class="nav nav-stacked" @endif>
                    <li @if(Request::is('bsod/bsod-adress') || Request::is('bsod/bsod-adress/*')) class="active" @endif>
                        <a href="{{ URL::route('BsodAdress.index')}}" >
                            <i class="icon-map-marker"></i>
                            <span>{!!trans('menu/menu_lang.eligibility_adres')!!}</span>
                        </a>
                    </li>
                </ul>

            </li>
        </ul>
    </div>
</nav>
