
    <header>
    <nav class="navbar navbar-fixed-top">
        <ul class="nav navbar-nav pull-left">
        <li class="dropdown">
        <a href="{{ URL::to('/')}}" style="font-size: 20px;"><i class="icon-circle"></i> {{{ Lang::get('header_lang.bsod') }}}  <span class="caret"></span></a>
        </li>
        </ul>
        <a id='reorder' class='toggle-nav btn pull-left' href='#'>
            <i class='icon-reorder'></i>
        </a>
        <ul class='nav pull-left'>
            @yield('bt_action_controller')
        </ul>
        <ul class='nav pull-right'>
            <li class='dropdown dark user-menu'>
                <a class='dropdown-toggle' data-toggle='dropdown' href='#'>
                  <span class="icon-cogs"></span>
                  <span class='user-name'>
                   teste
                  </span>
                  <b class='caret'></b>
                </a>
                <ul class='dropdown-menu'>
                  <li>
                    <a href='#' target="_blank">
                      <i class='icon-cog'></i>
                      <?php echo e(Lang::get('header_lang.profile'));?>
                    </a>
                  </li>
                  <li class='divider'></li>
                  <li>
                    <a href="#">
                        <i class='icon-signout'></i>
                        {{ Lang::get('header_lang.deconnexion') }}
                    </a>
                  </li>
                </ul>
            </li>
        </ul>
    </nav>
    </header>