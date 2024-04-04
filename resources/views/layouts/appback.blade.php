@php
    use Illuminate\Support\Facades\DB;
    use \App\Models\Master\Customer;

    $user = auth()->user();
    $roles = $user->getRoles();
    $functionality = \App\Models\WorkspaceFunctionality::functionalities( get_current_workspace()?->id );
    $subworkspace = get_current_workspace();
    $accounts_count = \App\Models\Account::where('active', ACTIVE)
        ->where('workspace_id', $subworkspace->id ?? null)
        ->count();
    $show_meeting_section = $accounts_count > 0 ? "admin" : "admin_DISABLE";
    // dd($roles,$show_meeting_section);
    $workspace = get_current_workspace();

    $customer = Customer::getCurrentSession();
@endphp

@include('layouts.header')

<?php

// Generate CSS classes for sidebar and content wrapper

$sidebarClasses = '';
$contentClasses = '';
if (isset($fullScreen)) {
    if ($fullScreen) {
        $sidebarClasses = 'd-none';
        $contentClasses = 'w-100';
    }
}
?>

<script>
    // Lista de permisos para usar en Vue
    // https://mmccaff.github.io/2018/11/03/laravel-permissions-in-vue-components/
    @auth
    window.Permissions = {!! json_encode(\Auth::user()->getAllPermissionsAttribute(), true) !!};
    @else
        window.Permissions = [];
    @endauth
    // console.log("Permisos: ", window.Permissions)
</script>

<div>
    <div id="pageloader">
        <img src="{{ asset('img/small_loader.gif') }}" alt="Cargando...">
        <span  style="color:blue" id="percentLoader"></span>
    </div>
</div>

<div id="app">
    @impersonating($guard = null)
        <div class="d-flex align-items-stretch bg-red text-center">

            <div class="col text-center">
                <a class="text-center text-white" href="{{ route('impersonate.leave') }}">Leave impersonation</a>
            </div>

        </div>
    @endImpersonating

    @if($customer && $customer->showStatusMessage('payment-missing'))
        <div class="d-flex align-items-stretch bg-red text-center">

            <div class="col text-center">
                ¡Tienes un pago vencido! Fecha de corte programado {{ $customer->platform_cutoff_date?->diffForHumans() ?? '--' }}. Comunícate con nuestro equipo para regularizar tus pagos.
            </div>

        </div>
    @endif

    @if($customer && $customer->hasStatusCode('inactive'))
        <div class="d-flex align-items-stretch bg-red text-center">

            <div class="col text-center">
                Plataforma suspendida. Fecha de corte programado {{ $customer->platform_cutoff_date?->diffForHumans() ?? '--' }}.
            </div>

        </div>
    @endif

    <div class="d-flex align-items-stretch">

        @unless(isset($fullScreen) && $fullScreen)

        <div class="nav-container <?= $sidebarClasses ?>">
            <div class="sidemenu-container">

                {{-- Sidemenu skeleton --}}

                <div class="skeleton-wrapper">
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                    <div class="skeleton purple"></div>
                </div>


                {{-- <v-app> --}}
                <side-menu :roles="{{ json_encode($roles) }}" :show_meeting_section="'{{ $show_meeting_section }}'" :functionality="{{ json_encode($functionality) }}"/>
                {{-- </v-app> --}}
            </div>
        </div>

        @endunless

        <div class="content-inner-small pb-0">
            <p class="">
                <b>Lo sentimos, la resolución de pantalla es muy pequeña para trabajar en el gestor. Es necesario
                    ingresar desde una PC o laptop y recomendable tener el navegador a pantalla completa.</b>
            </p>
        </div>
        <div class="content-inner pb-0 <?= $contentClasses ?>">

            @if (session('info'))
                <div class="row box-alerta">
                    <div class="col-md-8 offset-md-2">
                        <div class="alert alert-success alert-dismissible  mt-3 msg-alerta" role="alert">
                            {{ session('info') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                    </div>
                </div>
            @endif

            @if (isset($_GET['success']) && $_GET['success'] == 1)
                <div class="row box-alerta">
                    <div class="col-md-8 offset-md-2">
                        <div class="alert alert-success alert-dismissible  mt-3 msg-alerta" role="alert">
                            {{ $_GET['msg'] }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('modal-info'))
                <div class="modal fade" tabindex="-1" role="dialog" id="modal-info">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content" style="border-radius: 10px;">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title" style="color: white !important;">Aviso</h5>
                            </div>
                            <div class="modal-body">
                                {!! session('modal-info') !!}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Entendido</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif


                @yield('content')

                {{--
                    Dashboard skeleton
                ======================================== --}}

                @if (Route::is('home'))
                    <div class="dashboard skeleton-wrapper position-relative p-4">

                        <div class="row m-0">
                            <div class="col-12 card skeleton module-selector"></div>
                        </div>

                        <div class="row justify-content-between m-0 mt-4 bg-white">
                            <div class="skeleton card2"></div>
                            <div class="skeleton card2"></div>
                            <div class="skeleton card2"></div>
                            <div class="skeleton card2"></div>
                            <div class="skeleton card2"></div>
                        </div>

                        <div class="row m-0 mt-4 justify-content-between">
                            <div class="col-6 skeleton charts"></div>
                            <div class="col-6 skeleton charts"></div>
                        </div>
                    </div>

                @elseif (!Route::is('workspaces-list'))

                    <div class="table-gui skeleton-wrapper position-relative p-4">

                        <div class="row m-0">
                            <div class="col-12 card skeleton filters-selector"></div>
                        </div>

                        <div class="row m-0 mt-4">
                            <div class="col-12 px-0">
                                <div class="skeleton header-skeleton"></div>
                                <div class="body">
                                    @for($i = 1; $i <= 10; $i++)
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="skeleton bar"></div>
                                            </div>
                                            <div class="col-3">
                                                <div class="skeleton bar"></div>
                                            </div>
                                            <div class="col-3">
                                                <div class="skeleton bar"></div>
                                            </div>
                                            <div class="col-2 d-flex justify-content-between">
                                                <div class="skeleton icon"></div>
                                                <div class="skeleton icon"></div>
                                                <div class="skeleton icon"></div>
                                            </div>
                                        </div>
                                    @endfor

                                </div>
                            </div>
                        </div>
                    </div>

                @endif

        </div>
        <div id="content_polls" style="display: none;">
        <div class="box-valoracion hide" id="box_valoracion">
            <div class="box-closed" onclick="close_box_val()">
                <i class="mdi mdi-close-circle" id="i_close_circle"></i>
            </div>
            <div class="box-item-val val-init" id="box_val_init" onclick="showStars()">
                <div class="itemb">
                    <p id="box_val_init_btn_text">Ayúdanos a mejorar</p>
                </div>
            </div>
            <div class="box-item-val hide" id="box_val_stars">
                <div class="itemb">
                    <p id="box_val_stars_question"></p>
                </div>
                <div class="box-stars">
                    @for ($i = 1; $i < 11; $i++)
                        <div class="i-star" onclick="showIcons(@php echo $i @endphp)">
                            <i class="i-star-i mdi mdi-star-outline" id="@php echo 'i_star_'.$i @endphp"></i>
                        </div>
                    @endfor
                </div>
                <div class="box-saved">
                    <i class="mdi mdi-check-circle" id="i_saved_stars"></i>
                </div>
            </div>
            <div class="box-item-val hide" id="box_val_icons">
                <div class="itemb">
                    <p id="box_val_icons_question"></p>
                </div>
                <div class="box-icons">
                    <div id="box_val_icons_item_btn1" class="i-icon" data-char="" onclick="showComment(this)">
                        <div class="i-img">
                            <img id="box_val_icons_item_icon1" src="{{ asset('img/valoracion/automatica.png') }}" alt="automatica">
                        </div>
                        <span id="box_val_icons_item_text1"></span>
                    </div>
                    <div id="box_val_icons_item_btn2" class="i-icon" data-char="" onclick="showComment(this)">
                        <div class="i-img">
                            <img id="box_val_icons_item_icon2" src="{{ asset('img/valoracion/didactica.png') }}" alt="didactica">
                        </div>
                        <span id="box_val_icons_item_text2"></span>
                    </div>
                    <div id="box_val_icons_item_btn3" class="i-icon" data-char="" onclick="showComment(this)">
                        <div class="i-img">
                            <img id="box_val_icons_item_icon3" src="{{ asset('img/valoracion/innovadora.png') }}" alt="innovadora">
                        </div>
                        <span id="box_val_icons_item_text3"></span>
                    </div>
                    <div id="box_val_icons_item_btn4" class="i-icon" data-char="" onclick="showComment(this)">
                        <div class="i-img">
                            <img id="box_val_icons_item_icon4" src="{{ asset('img/valoracion/practica.png') }}" alt="practica">
                        </div>
                        <span id="box_val_icons_item_text4"></span>
                    </div>
                </div>
                <div class="box-saved">
                    <i class="mdi mdi-check-circle" id="i_saved_icons"></i>
                </div>
            </div>
            <div class="box-item-val hide" id="box_val_comment">
                <div class="itemb">
                    <p id="box_val_comment_question"></p>
                </div>
                <div class="box-comment">
                    <textarea class="form-control txtarea_val" rows="4" placeholder="" id="box_val_comment_hint"></textarea>
                </div>
                <div class="box-send">
                    <button type="button" class="btn-send" id="btn_send_val" onclick="sendComment()">
                        <div class="hide spinner_btn_send" id="spinner_btn_send">
                            <span class="spinner"></span>
                        </div>
                        <span id="txt_btn_send">Enviar</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="box-valoracion-final hide val-final" id="box_val_final">
            <div class="itemb">
                <p>¡Gracias por tu feedback!</p>
            </div>
        </div>
        </div>
    </div>
</div>

@section('js')
    <script>
        @if (session('nps'))
            var nps_data = @php echo json_encode(session('nps')); @endphp;
        @endif

        const URL_SAVE_NPS = "@php echo env('URL_SAVE_NPS'); @endphp";
        const USER_NAME = "{{ Auth::user()->name }}";
        const USER_EMAIL = "{{ Auth::user()->email }}";
        const USER_DOCUMENT = "{{ Auth::user()->document }}";
        const USER_WORKSPACE = "{{ $workspace?->name }}";
        const USER_WORKSPACE_SLUG = "{{ $workspace?->slug }}";
    </script>
    <script src="{{ asset('js/sweetalert2.js') }}"></script>
    <script src="{{ asset('js/app.js?v=3.3422-' . date('Y-W')) }}"></script>
    <script src="{{ asset('js/custom.js?v=3.3422-' . date('Y-W')) }}"></script>

    <script>
        $(document).ready(function () {
            $('#content_polls').css('display','block');
        });
    </script>

    @stack('libraries')

    @stack('scripts')

    @if (session('modal-info'))
        <script>
            $('#modal-info').modal('show');
        </script>
    @endif

    <script>
        $('.btndelete').on('click', function(e) {
            e.preventDefault();

            swal({
                    title: "¿Estás seguro?",
                    text: "Al eliminar este registro también se eliminará toda la data relacionada.",
                    icon: "warning",
                    buttons: true,
                    buttons: ["Cancelar", "OK"],
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(this).parents("form").submit();
                    }
                });

        });
        $('.btnstatus').on('click', function(e) {
            e.preventDefault();

            swal({
                    title: "¿Desea cambiar el estado de este registro?",
                    text: "",
                    icon: "warning",
                    buttons: true,
                    buttons: ["Cancelar", "OK"],
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(this).parents("form").submit();
                    }
                });

        });
        //
        setTimeout(function() {
            $('.box-alerta').fadeOut().remove();
        }, 3000);
        //

        // $(window).bind('scroll', function() {

        //   var navHeight = $('.page-header').position();

        //   if ($(window).scrollTop() > navHeight.top) {
        //     console.log('s');
        //     $('.page-header').addClass('headfixed');
        //   } else {
        //     $('.page-header').removeClass('headfixed');
        //   }
        // });

        $('.collapse').collapse()
    </script>
    <script>
        function switchPlatform( platform ) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "post",
                url: "/switch_platform",
                data: { "platform": platform },
                dataType: "json",
                success: function (response) {
                    if(response.data.platform == 'induccion')
                        window.location = '/induccion'
                    else
                        window.location = '/'
                }
            });
        }
    </script>
    <style>
        .list-unstyled {
            position: initial;
            overflow-y: scroll;
            height: 100vh;
        }

        .content-inner {
            /*position: relative;*/
            /*width: calc(100%);*/
        }
    </style>
@show

<!-- Asigna (a input) Media Modal -->
@include('media.asigna_modal')

@if (config('app.CHATBOT_HUBSPOT'))
<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer src="{{config('app.CHATBOT_HUBSPOT')}}"></script>
<!-- End of HubSpot Embed Code -->
@endif
