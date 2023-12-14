<!DOCTYPE html>
<html>
<head>
    <title>{{$title}}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif;font-size: 14px">
    <div style="text-align: center"><img src="{{$subworkspace['subworkspace_logo']}}" alt=""></div>
    <h4 style="text-align: center;font-weight: bolder;margin:0">FORMATO DC-3</h4>
    <h4 style="text-align: center;font-weight: bolder;margin:0">CONSTANCIA DE COMPETENCIAS O DE HABILIDADES LABORALES</h4>
    <table style="border: 2px solid black;width:100%; border-collapse: collapse;">
        <tr style="text-align: center;background: black;color:white;">
            <td colspan="2">DATOS DEL TRABAJADOR</td> 
        </tr> 
        <tr>
            <td style="padding: 5px 5px 10px 5px;" colspan="2">
                <div>Nombre (Anotar apellido paterno, apellido materno y nombre(s))</div>
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-weight: bolder">{{$user['name']}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="border-top: 2px solid black;">
            <td style="padding: 5px 5px 0px 5px;">
                <div>Clave Única de Registro de Población</div>
                @php
                    $curp = str_split($user['curp']);
                @endphp
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        @foreach ($curp as $n)
                            @if (!$loop->last)
                                <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                            @else
                                <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                            @endif
                        @endforeach
                    </tr>
                </table>
            </td>
            <td style="padding: 5px 5px 10px 5px;border-left: 2px solid black">
                <div>Ocupación especifica (Catálogo Nacional de Ocupaciones) <sup>1/</sup></div>
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-weight: bolder">{{$user['occupation']}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="border-top: 2px solid black;">
            <td style="padding: 5px 5px 10px 5px;" colspan="2">
                <div>Puesto</div>
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-weight: bolder">{{$user['position']}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="width:100%; border-collapse: collapse;border: 2px solid black;margin-top:10px">
        <tr style="text-align: center;background: black;color:white;padding:8px 0px 8px 0px;">
            <td colspan="2">DATOS DE LA EMPRESA</td> 
        </tr> 
        <tr>
            <td style="padding: 5px 5px 10px 5px;" colspan="2">
                <div>Nombre o razón social (En caso de persona fisica, anotar apellido paterno, apellido materno y nombre(s))</div>
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-weight: bolder">{{$subworkspace['name_or_social_reason']}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <td style="padding: 5px 5px 0px 5px; border-top: 2px solid black;" colspan="2">
            <div>Registro Federal de Contribuyentes con homoclave (SHCP)</div>
            @php
                $shcp = str_split($subworkspace['shcp']);
            @endphp
            <table style="width:100%; border-collapse: collapse;">
                <tr>
                    @foreach ($shcp as $n)
                        @if (!$loop->last)
                            <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                        @else
                            <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                        @endif
                    @endforeach
                </tr>
            </table>
        </td>
    </table>
    <div style="border: 2px solid black;margin-top:10px">
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align: center;background: black;color:white;padding:0px 0px 0px 0px;">
                <td colspan="12">DATOS DEL PROGRAMA DE CAPACITACIÓN, ADIESTRAMIENTO Y PRODUCTIVIDAD</td> 
            </tr> 
            <tr style="border-top: 2px solid black;">
                <td style="padding: 5px 5px 10px 5px;" colspan="12">
                    <div>Nombre del curso</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">{{$course['name']}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="border-top:2px solid black">
                <td style="padding: 5px 5px 10px 5px;border-right: 2px solid black;">
                    <div>Duración en horas</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">{{$course['duration']}}</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 10px 5px;border-right: 2px solid black;">
                    <div>Periodo de ejecución</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td>De:</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Año</div>
                    @php
                        $init_date_course_year = str_split($course['init_date_course_year']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($init_date_course_year as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Mes</div>
                    @php
                        $init_date_course_month = str_split($course['init_date_course_month']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($init_date_course_month as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Día</div>
                    @php
                        $init_date_course_day = str_split($course['init_date_course_day']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($init_date_course_day as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="border-right: 2px solid black;border-left: 2px solid black;">
                    <div style="text-align: center">a</div>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Año</div>
                    @php
                        $final_date_course_year = str_split($course['final_date_course_year']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($final_date_course_year as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Mes</div>
                    @php
                        $final_date_course_month = str_split($course['final_date_course_month']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($final_date_course_month as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 0px 5px;border-right: 2px solid black">
                    <div style="text-align: center">Día</div>
                    @php
                        $final_date_course_day = str_split($course['final_date_course_day']);
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($final_date_course_day as $n)
                                @if (!$loop->last)
                                    <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @else
                                    <td style="padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                                @endif
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="border-top: 2px solid black;">
                <td style="padding: 5px 5px 10px 5px;" colspan="12">
                    <div>Área temática del curso <sup>2/</sup></div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">{{$course['catalog_denomination_dc3']}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="border-top: 2px solid black;">
                <td style="padding: 5px 5px 10px 5px;" colspan="12">
                    <div>Nombre del agente capacitador o STPS <sup>3/</sup></div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">{{$course['instructor']}}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div style="border: 2px solid black;margin-top:10px">
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align: center;font-weight: bolder">
                <td colspan="12">Los datos se asientan en esta constancia bajo protesta de decir verdad, apercibidos de las responsabilidad en que incurre todo aquel que no conduce la verdad</td> 
            </tr> 
            <tr style="text-align: center">
                <td style="padding: 5px 5px 10px 5px;" colspan="6">
                    <div>Instructor o tutor</div>
                    <div>{{$course['instructor']}}</div>
                    <img width="170px" src="{{$course['instructor_signature']}}" alt="" srcset="">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr style="text-align: center;">
                            <td style="font-weight: bolder;">
                                <span style="border-top:1px solid black;">
                                    Nombre y Firma
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 10px 5px;" colspan="6">
                    <div>Patrón o representante legal <sup>4/</sup></div>
                    <div>{{$course['legal_representative']}}</div>
                    <img width="170px" src="{{$course['legal_representative_signature']}}" alt="" srcset="">
                    <table style="width:100%; border-collapse: collapse;">
                        <tr style="text-align: center">
                            <td style="font-weight: bolder;">

                                <span style="border-top:1px solid black;">
                                    Nombre y Firma
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div style="margin-top:10px">
        <div>
            <div> <span style="font-weight: bold">INSTRUCCIONES</span></div>
            <div>
                <ul style="list-style-type: none;">
                    <li>- Llenar la máquina o con la letra de molde.</li>
                    <li>- Deberá entregarse al trabajador dentro de los veinte días hábiles siguientes al término de capacitación aprobado.</li>    
                    <li><sup>1/</sup> Las áreas y subáreas ocupacionales del Catálogo Nacional de Ocupaciones se encuentra disponible al reverso de este formato y en la página <a href="https://www.stps.gob.mx" target="_BLANK">www.stps.gob.mx</a></li>    
                    <li><sup>2/</sup> Las áreas temáticas de los cursos disponibles en el reverso de este formato y en la página <a href="https://www.stps.gob.mx" target="_BLANK">www.stps.gob.mx</a></li>
                    <li><sup>3/</sup> Cursos impartidos por el área competente de la Secretaria del Trabajo y Previsión Social</li>
                    <li><sup>4/</sup> Para empresas con menos de 51 trabajadores. Para empresas con más de 50 trabajadores firmaría el representante del patrón ante la Comisión de capacitación, adiestramiento y productividad</li>
                    <li><sup>5/</sup> Solo para empresas on mas de 50 trabajadores</li>
                    <li>* Dato no obligatorio.</li>
                </ul>
            </div>
            {{-- <div style="text-align: right">
                DC-3
                ANVERSO
            </div>                 --}}
        </div>
    </div>
    <table style="width: 100%">
        <tr>
            <td colspan="12">
                <div style="font-weight: bold;text-align: center;margin-top:40px">CLAVES Y DENOMINACIONES DE ÁREAS Y SUBÁREAS DEL CATÁLOGO NACIONAL DE OCUPACIONES</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">CLAVE DEL <br> ÁREA/SUBÁREA</td>
            <td colspan="3" align="center">DENOMINACIÓN</td>
            <td colspan="3" align="center">CLAVE DEL <br> ÁREA/SUBÁREA</td>
            <td colspan="3" align="center">DENOMINACIÓN</td>
        </tr>
        @foreach ($national_occupations_catalog as $noc)
            @if ($loop->iteration % 2 == 1)
                <tr>
            @endif
            <td colspan="3">
                {{ $noc['code'] }}
            </td>
            <td colspan="3">
                {{ $noc['name'] }}
            </td>
            @if ($loop->iteration % 2 == 0 || $loop->last)
                </tr>
            @endif
        @endforeach

        <tr>
            <td colspan="12">
                <div style="font-weight: bold;text-align: center;margin-top:40px">CLAVES Y DENOMINACIONES DEL CATÁLOGO DE ÁREAS TEMÁTICAS DE LOS CURSOS</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">CLAVE DEL <br> ÁREA/SUBÁREA</td>
            <td colspan="3" align="center">DENOMINACIÓN</td>
            <td colspan="3" align="center">CLAVE DEL <br> ÁREA</td>
            <td colspan="3" align="center">DENOMINACIÓN</td>
        </tr>
        @foreach ($catalog_denominations as $cd)
            @if ($loop->iteration % 2 == 1)
                <tr>
            @endif
            <td colspan="3">
                {{ $cd['code'] }}
            </td>
            <td colspan="3">
                {{ $cd['name'] }}
            </td>
            @if ($loop->iteration % 2 == 0 || $loop->last)
                </tr>
            @endif
        @endforeach
    </table>
</body>
</html>
