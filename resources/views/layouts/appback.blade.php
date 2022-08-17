@php
use Illuminate\Support\Facades\DB;

$user = auth()->user();
$permisos = [];

$group_cursos = ['modulos', 'segmentacion', 'carreras'];
$group_contenido = ['anuncios', 'encuestas', 'multimedia', 'glosario', 'vademecum', 'videoteca', 'tags'];
$group_aulas = ['meetings', 'accounts'];
$group_resumen = ['home', 'learning_analytics', 'resumen_encuesta'];
$group_entrenadores = ['entrenadores', 'checklist'];
$group_exportar = ['reportes', 'conferencias'];
$group_soporte = ['notificaciones', 'faq', 'ayuda', 'soporte'];
$group_herramienta = ['reinicio_usuarios', 'proceso_masivo', 'migrar_avance', 'compatibles'];
$group_auditoria = ['incidencias', 'errores', 'auditoria'];
$group_roles = ['users'];

if ($user->isAn('super-user')) {
    $permisos = ['all'];
} elseif ($user->isAn('config')) {
    $permisos = array_merge($group_cursos, $group_contenido);
} elseif ($user->isAn('admin')) {
    $permisos = array_merge($group_cursos, $group_contenido, $group_entrenadores, $group_exportar);
} elseif ($user->isAn('content-manager')) {
    $permisos = array_merge($group_contenido);
} elseif ($user->isAn('trainer')) {
    $permisos = array_merge($group_cursos, $group_entrenadores, $group_exportar);
} elseif ($user->isAn('reports')) {
    $permisos = array_merge($group_exportar);
} else {
    $permisos = ['all'];
}
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
    </div>
</div>

<div id="app">
    <div class="d-flex align-items-stretch">

        <div class="nav-container <?= $sidebarClasses ?>">
            <div class="sidemenu-container">
                {{-- <v-app> --}}
                <side-menu :options="{{ json_encode($permisos) }}" />
                {{-- </v-app> --}}
            </div>
        </div>

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

        </div>
    </div>
</div>

@section('js')
    <script src="{{ asset('js/sweetalert2.js') }}"></script>
    <script src="{{ asset('js/app.js?v=' . date('Ymd') . '-2') }}"></script>

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
