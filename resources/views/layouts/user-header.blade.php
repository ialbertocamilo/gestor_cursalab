<div class="main-header">

    <div class="content {{ Request::path() == 'reset_password' ? 'justify-content-between' : '' }}">

        {{-- LOGO  --}}
        @if(Request::path() == 'reset_password')
            <div>
                <a href="/welcome">
                    <img src="/img/logo_cursalab_v2_black.png"
                         width="150px"
                         alt="Cursalab">
                </a>
            </div>
        @endif
        {{-- LOGO  --}}


        {{-- PERFIL  --}}

        <div class="d-flex">

            <div class="btn-group">
                <button class="d-flex align-items-center btn btn-unset dropdown-toggle"
                        type="button" data-toggle="dropdown" aria-expanded="false">
                    <div class="username-rol">
                        <p class="username">{{ Auth::user()->name }}</p>
                        <p class="rol">{{ Auth::user()->email_gestor }}</p>
                    </div>
                    <div class="avatar-image mr-2">
                        <img class="avatar" src="{{ asset('img/avatar-default.png') }}" />
                    </div>
                </button>
                <div class="dropdown-menu dropdown-header-menu shadow-md">
                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/workspaces/criterios">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-clipboard-list"></span>
                        </div>
                        <span>Criterios</span>
                    </a>
                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/users">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-users-cog"></span>
                        </div>
                        <span>Administradores</span>
                    </a>
                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/roles">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-user-shield"></span>
                        </div>
                        <span>Roles</span>
                    </a>
                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/preguntas-frecuentes">
                        <div class="dropdown-icon-width">
                            <span class="far fa-question-circle"></span>
                        </div>
                        <span>Preguntas Frecuentes</span>
                    </a>
                    
                    <hr>

                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/reset_password">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-shield-alt"></span>
                        </div>
                        <span>Actualizar contraseña</span>
                    </a>
                    <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/workspaces/list">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-th-large"></span>
                        </div>
                        <span>Mis workspaces</span>
                    </a>
                    <a class="dropdown-item py-2 dropdown-item-custom text-body"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <div class="dropdown-icon-width">
                            <span class="fas fa-sign-out-alt"></span>
                        </div>
                        <span>Cerrar sesión</span>
                        <form id="logout-form" action="{{ route('logout') }}"
                              method="POST" style="display: none;">
                            @csrf
                        </form>
                    </a>
                </div>
            </div>

            {{--  <div class="vertical"></div>
             <div class="main-header-options">

                 @if (Request::path() != 'workspaces/list')
                 <a href="/workspaces/list"
                     class="mr-3">
                     <i class="fa fa-th"></i>
                 </a>
                 @endif

                 <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                     <i class="fas fa-sign-out-alt"></i>
                 </a>
                 <form id="logout-form" action="{{ route('logout') }}"
                     method="POST" style="display: none;">
                     @csrf
                 </form>
             </div> --}}
        </div>
        {{-- PERFIL  --}}

    </div>

</div>
