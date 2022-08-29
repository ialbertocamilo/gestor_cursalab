<div class="main-header">
    <div class="content">
        <div class="username-rol">
            <p class="username">{{ Auth::user()->name }}</p>
            <p class="rol">{{Auth::user()->email}}</p>
        </div>
        <div class="avatar-image">
            <img class="avatar" src="{{ asset('img/avatar-default.png') }}" />
        </div>
        <div class="vertical"></div>
        <div class="main-header-options">

            <a href="/workspaces/list"
                class="mr-3">
                <i class="fa fa-th"></i>
            </a>

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
</div>
