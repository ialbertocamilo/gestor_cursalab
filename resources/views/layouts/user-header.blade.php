<div class="main-header">
    <div class="content {{ Request::path() == 'reset_password' ? 'justify-content-between' : '' }}">

        {{-- LOGO  --}}
        @if(Request::path() == 'reset_password')
        <div>
            <img src="/img/we-connect-logo.png"
                 width="70px" 
                 alt="We connect">       
        </div>
        @endif
        {{-- LOGO  --}}


        {{-- PERFIL  --}}
        <div class="d-flex">
            <div class="btn-group">
                <div class="py-0 d-flex">
                    <div class="username-rol">
                        <p class="username">{{ Auth::user()->name }}</p>
                        <p class="rol">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="avatar-image">
                        <img class="avatar" src="{{ asset('img/avatar-default.png') }}" />
                    </div>
                </div>

                <button type="button" class="btn dropdown-toggle dropdown-toggle-split" 
                        id="dropdownMenuProfile" 
                        data-toggle="dropdown" aria-haspopup="true" 
                        aria-expanded="false" data-reference="parent" 
                        >
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right-custom" 
                     aria-labelledby="dropdownMenuProfile">
                    <a class="dropdown-item" href="/reset_password">
                        Actualizar contraseña
                    </a>
                </div>
            </div>


            <div class="vertical"></div>
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
                <form id="logout-form"
                      action="{{ route('logout') }}"
                      method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        {{-- PERFIL  --}}

    </div>
</div>
