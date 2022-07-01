<nav class="side-navbar">

                <ul class="list-unstyled pt-0">
                    <li>
                        <div class="py-3 px-2">
                            <img src="{{ asset('img/ucfp_logo_blanco.png') }}" alt="Farmacias peruanas" class="img-fluid" width="150">
                        </div>
                    </li>

                    <li class="">
                        <a class="nav-link">RESUMEN</a>
                    </li>

                    <li class="{{ Request::is('home*') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="nav-link"><i class="fas fa-tachometer-alt"></i> General</a>
                    </li>

                    <li class="{{ Request::is('dashboard_pbi*') ? 'active' : '' }}">
                        <a href="{{ route('dashboard_pbi') }}" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
                    </li>

                    @can('resumen_encuesta.index')
                        <li class="{{ Request::is('resumen_encuesta*') ? 'active' : '' }}">
                            <a href="{{ route('resumen_encuesta.index') }}" class="nav-link"><i class="fas fa-poll"></i> Encuestas</a>
                        </li>
                    @endcan

                    <li class="{{ Request::is('aulas_virtuales*') ? 'active' : '' }}">
                        <a href="{{ route('aulas_virtuales') }}" class="nav-link"><i class="fas fa-chalkboard-teacher"></i> Aulas
                            virtuales</a>
                    </li>

                    @can('abconfigs.index')
                        <li class="">
                            <h5 class="nav-link">CONFIGURACIÓN</h5>
                        </li>

                        <li
                            class="{{ ( Request::is('modulos')  || Request::is('abconfigs/index') || Request::is('categorias*') || Request::is('cursos*') || Request::is('posteos*') || Request::is('preguntas*') ) ? 'active' : '' }}">
{{--                            <a href="{{ route('abconfigs.list') }}" class="nav-link"><i class="fas fa-cog"></i> Módulos</a>--}}
                            <a href="{{ route('abconfigs.index') }}" class="nav-link"><i class="fas fa-cog"></i> Módulos</a>
                        </li>

                        <li class = "{{ ( Request::is('abconfigs/*/carreras') || Request::is('carreras*') ) ? 'active' : '' }}">
                            <a href="{{ route('carreras.index') }}" class="nav-link"><i class="fas fa-th-large"></i> Carreras</a>
                        </li>

                        <li class="{{ ( Request::is('curriculas_grupos*') ) ? 'active' : '' }}">
                            <a href="{{ route('curriculas_grupos') }}" class="nav-link"><i class="fas fa-th-large"></i> Segmentación</a>
                        </li>
                    @endcan
                    
                    <!--  -->
                    <li class="">
                        <h5 class="nav-link">GESTIONA TU CONTENIDO</h5>
                    </li>
                    @can('boticas.index')
                        <li class="{{ Request::is('boticas*') ? 'active' : '' }}">
                            <a href="{{ route('boticas.index') }}" class="nav-link"><i class="fas fa-briefcase-medical"></i> Sedes</a>
                        </li>
                    @endcan
                    @can('cargos.index')
                        <li class="{{ Request::is('cargos*') ? 'active' : '' }}">
                            <a href="{{ route('cargos.index') }}" class="nav-link"><i class="fas fa-user-tie"></i> Cargos</a>
                        </li>
                    @endcan


                    <li class="{{ Request::is('auditoria*') ? 'active' : '' }}">
                        <a href="{{ route('auditoria.index') }}" class="nav-link"><i class="fas fa-balance-scale"></i> Auditoria</a>
                    </li>

                    {{-- @can('criterios.index')
                    @can('criterios.index')
                    <li class="{{ Request::is('criterios*') ? 'active' : '' }}">
                      <a href="{{ route('criterios.index') }}" class="nav-link"><i class="fas fa-clipboard-list"></i> Criterios</a>
                    </li>
                    @endcan --}}
                    @can('criterios.index')
                        <li class="{{ Request::is('tipo_criterio*') ? 'active' : '' }}">
                            <a href="{{ route('tipo_criterio.index') }}" class="nav-link"><i class="fas fa-clipboard-list"></i> Tipo Criterios</a>
                        </li>
                    @endcan
                    @can('usuarios.index')
                        <li class="{{ Request::is('usuarios*') ? 'active' : '' }}">
                            <a href="{{ route('usuarios.list') }}" class="nav-link"><i class="fas fa-users"></i> Usuarios</a>
                        </li>
                    @endcan

                    @can('grupos.index')
                        <li class="{{ Request::is('grupos*') ? 'active' : '' }}">
                            <a href="{{ route('grupos.index') }}" class="nav-link"><i class="fas fa-grip-horizontal"></i> Código de
                                Matrícula</a>
                        </li>
                    @endcan

                    @can('encuestas.index')
                        <li class="{{ (Request::is('encuestas/*') || Request::is('encuestas_preguntas/*')) ? 'active' : '' }}">
                            <a href="{{ route('encuestas.index') }}" class="nav-link"><i class="fas fa-pencil-alt"></i> Encuestas</a>
                        </li>
                    @endcan

                    @can('anuncios.index')
                        <li class="{{ Request::is('anuncios*') ? 'active' : '' }}">
                            <a href="{{ route('anuncios.index') }}" class="nav-link"><i class="far fa-newspaper"></i> Anuncios</a>
                        </li>
                    @endcan

                    @can('glosarios.index')
                        <li class="{{ Request::is('glosarios*') ? 'active' : '' }}">
                            <a href="{{ route('glosarios.index') }}" class="nav-link"><i class="fas fa-book-open"></i> Glosario</a>
                        </li>
                    @endcan
                    @can('compatibles.index')
                        <li class="{{ Request::is('compatibles*') ? 'active' : '' }}">
                            <a href="{{ route('compatibles.index') }}" class="nav-link"><i class="fas fa-equals"></i> Compatibles</a>
                        </li>
                    @endcan
                    <li class="{{ (Request::is('multimedia') || Request::is('multimedia/*')) ? 'active' : '' }}">
                        <a href="{{ route('multimedia.index') }}" class="nav-link"><i class="fas fa-photo-video"></i> Multimedia</a>
                    </li>
                    @can('vademecum.index')
                        <li class="{{ (Request::is('vademecum/*') || Request::is('vademecum/*')) ? 'active' : '' }}">
                            <a href="{{ route('vademecum.index') }}" class="nav-link"><i class="fas fa-caret-square-right"></i> Vademecum</a>
                        </li>
                    @endcan
                    @can('videoteca.index')
                        <li class="{{ (Request::is('videoteca/*') || Request::is('videoteca/*')) ? 'active' : '' }}">
                            <a href="{{ route('videoteca.list') }}" class="nav-link"><i class="fas fa-caret-square-right"></i> Videoteca</a>
                        </li>
                    @endcan

                    <li class="{{ (Request::is('tags/*') || Request::is('tags/*')) ? 'active' : '' }}">
                        <a href="{{ route('tags.index') }}" class="nav-link"><i class="fas fa-tags"></i> Tags</a>
                    </li>

                    @can('entrenamiento.index')
                        <li class="">
                            <h5 class="nav-link">ENTRENADORES Y CHECKLIST</h5>
                        </li>

                        <li class="{{ Request::is('entrenamiento/entrenador/list') ? 'active' : '' }}">
                            <a href="{{ route('entrenamiento.entrenadores') }}" class="nav-link"><i class="fas fa-user-graduate"></i> Entrenadores</a>
                        </li>

                        <li class="{{ Request::is('entrenamiento/checklist/list') ? 'active' : '' }}">
                            <a href="{{ route('entrenamiento.checklist') }}" class="nav-link"><i class="fas fa-tasks"></i>
                                Checklists</a>
                        </li>
                    @endcan

                    @can('exportar.index')
                        <li class="">
                            <h5 class="nav-link">EXPORTAR</h5>
                        </li>

                        <li class="{{ Request::is('exportar/node') ? 'active' : '' }}">
                            <a href="{{ route('exportar.node') }}" class="nav-link"><i class="fas fa-download"></i> Reportes</a>
                        </li>

                        <li class="{{ Request::is('exportar_conferencias/node') ? 'active' : '' }}">
                            <a href="{{ route('exportar_conferencias.index') }}" class="nav-link"><i class="fas fa-download"></i>
                                Conferencias</a>
                        </li>
                    @endcan

                    <li class="">
                        <h5 class="nav-link">ATENCIÓN AL CLIENTE</h5>
                    </li>

                    @can('notificaciones_push.index')
                        <li class="{{ Request::is('notificaciones_push*') ? 'active' : '' }}">
                            <a href="{{ route('notificaciones_push.index') }}" class="nav-link"><i class="fas fa-envelope-open-text"></i>
                                Notificaciones Push</a>
                        </li>
                    @endcan

                    @can('pregunta_frecuentes.index')
                        <li class="{{ Request::is('pregunta_frecuentes*') ? 'active' : '' }}">
                            <a href="{{ route('pregunta_frecuentes.index') }}" class="nav-link"><i class="far fa-question-circle"></i>
                                Preguntas Frecuentes</a>
                        </li>
                    @endcan

                    @can('ayudas.index')
                        <li class="{{ Request::is('ayudas*') ? 'active' : '' }}">
                            <a href="{{ route('ayudas.index') }}" class="nav-link"><i class="fas fa-hands-helping"></i> Ayuda</a>
                        </li>
                    @endcan
                    @can('usuarios_ayuda.show')
                        <li class="{{ Request::is('usuarios_ayuda*') ? 'active' : '' }}">
                            @php
                                $pendientes = DB::table('usuario_ayuda')->where('estado','pendiente')->select('id')->count();
                            @endphp
                            <a href="{{ route('usuarios_ayuda.show') }}" class="nav-link">
                                <i class="fas fa-headset"></i>Soporte
                                <span  class="ml-2 badge badge-light">{{$pendientes}}</span>
                            </a>
                        </li>
                    @endcan
                    <!--  -->
                    <li class="">
                        <h5 class="nav-link">HERRAMIENTAS</h5>
                    </li>

                    @can('tools.masivo')
                        <li class="{{ Request::is('masivo/index') ? 'active' : '' }}">
                            <a href="{{ route('masivo.index') }}" class="nav-link"><i class="fas fa-share-square"></i> Procesos
                                Masivos</a>
                        </li>
                    @endcan
                    @can('usuarios.index_reinicios')
                        <li class="{{ Request::is('masivo/usuarios*') ? 'active' : '' }}">
                            <a href="{{ route('usuarios.index_reinicios') }}" class="nav-link"><i class="fas fa-redo-alt"></i>Usuarios reinicios</a>
                        </li>
                    @endcan

                    @can('users.index')
                        <li class="">
                            <h5 class="nav-link">ADMINISTRACIÓN</h5>
                        </li>

                        <li class="{{ Request::is('users*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="nav-link"> <i class="fas fa-users-cog"></i> Administradores</a>
                        </li>
                    @endcan

                    @can('roles.index')
                        <li class="{{ Request::is('roles*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="nav-link"> <i class="fas fa-user-tie"></i> Roles</a>
                        </li>
                    @endcan

                    @can('permisos.index')
                        <li class="{{ Request::is('permisos*') ? 'active' : '' }}">
                            <a href="{{ route('permisos.index') }}" class="nav-link"> <i class="fas fa-plus-circle"></i> Permisos</a>
                        </li>
                    @endcan
                    @can('incidencias.index')
                        <li class="{{ Request::is('incidencias*') ? 'active' : '' }}">
                            <a href="{{ route('incidencias.index') }}" class="nav-link"><i class="fas fa-exclamation-triangle"></i>Incidencias</a>
                        </li>
                    @endcan
                    @can('usuario_vigencias.index')
                        <li class="{{ Request::is('usuario_vigencias*') ? 'active' : '' }}">
                            <a href="{{ route('usuario_vigencias.index') }}" class="nav-link"><i class="fas fa-user-clock"></i> Vigencia
                                de usuarios</a>
                        </li>
                    @endcan

                </ul>
            </nav>