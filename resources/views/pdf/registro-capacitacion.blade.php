<!DOCTYPE html>
<html>
<head>
    <title
        {{ $course['registro_capacitacion']->certificateCode }}
    </title>
</head>
<body>

<style>

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
    }

    p, span {
        font-size: 11px;
    }

    .main-title {
        text-align: center;
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 15px 0;
    }

    .title-wrapper {
        text-align: center; background: black; color:white;
    }

    .table-title {
        font-weight: 700;
        height: 24px;
    }

    .table-form {
        border: 2px solid black;
        width:100%;
        border-collapse: collapse;
    }

    .row {
        border: 2px solid black;
    }

    .cell {
        border-right: 2px solid black;
    }

    .cell-label {
        font-size: 11px;
        padding-left: 10px;
    }

    .cell-value {
        font-weight: 700;
        font-size: 16px;
        padding-left: 10px;
        min-height: 21px;
    }

    .cell-number {
        position: relative;
        vertical-align: bottom;
    }

    .topics {
        min-height: 50px;
    }

    .topics p,
    .topics span,
    .topics li {
        font-family: Arial, Helvetica, sans-serif !important;
        font-size: 11px !important;
    }

    .margin-top {
        margin-top: 35px
    }

    .margin-bottom {
        margin-bottom: 35px;
    }

    .text-center {
        text-align: center;
    }

    .number-wrapper span {
        display: block;
        float: left;
        padding-left: 0px;
        padding-top: 5px;
        font-weight: inherit;
        font-size: inherit;
    }

    .number-separator {
        border-right: 2px solid black;
        width: 1px;
        height: inherit;
        margin-top: 8px;
        padding-right: 3px;
        text-align: center;
        float: left;
        display: none;
    }

</style>


<h4 class="main-title">
    {{ $course['registro_capacitacion']->certificateCode }}
</h4>

<table class="table-form">

    <tr class="title-wrapper">
        <td colspan="3" class="table-title">
            DATOS DE "LA EMPRESA"
        </td>
    </tr>

    <tr class="row">
        <td class="cell" colspan="3">
            <div class="cell-label">
                Razón social
            </div>
            <div class="cell-value">
                {{ $company->businessName ?? '' }}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell" style="width: 45%">
            <div class="cell-label">
                RUC
            </div>
            <div class="cell-value cell-number">
                @foreach (str_split($company->businessNumber ?? '') as $char)
                    {{ $char }}
                    <div class="number-separator "></div>
                @endforeach
            </div>
        </td>

        <td class="cell">
            <div class="cell-label">
                Actividad económica
            </div>
            <div class="cell-value">
                {{ $company->economicActivity ?? '' }}
            </div>
        </td>

        <td class="cell">
            <div class="cell-label">
                Con código CIIU Nº
            </div>
            <div class="cell-value cell-number">
                @foreach (str_split($company->CIIU ?? '') as $char)
                    {{ $char }}
                    <div class="number-separator "></div>
                @endforeach
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell" colspan="3">
            <div class="cell-label">
                Dirección de la sede
            </div>
            <div class="cell-value">
                {{ $address }}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell">
            <div class="cell-label">
                Número de trabajadores del centro laboral
            </div>
            <div class="cell-value">
                {{ $workersCount }}
            </div>
        </td>

        <td class="cell"  colspan="2">
            <div class="cell-label">
                Capacitador y encargado del registro
            </div>
            <div class="cell-value">
                {{ $trainer->name ?? '' }}
            </div>
        </td>
    </tr>
</table>

<table class="table-form margin-top">

    <tr class="title-wrapper">
        <td colspan="2" class="table-title">
            DATOS DEL “TRABAJADOR"
        </td>
    </tr>

    <tr class="row">
        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Nombre completo
            </div>
            <div class="cell-value">
                {{$user['name']}} {{$user['lastname']}} {{$user['surname']}}
            </div>
        </td>

        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Documento de identificación
            </div>
            <div class="cell-value cell-number">
                @foreach (str_split($user['document']) as $char)
                    {{ $char }}
                    <div class="number-separator"></div>
                @endforeach
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Cargo
            </div>
            <div class="cell-value">
                {{ $job_position }}
            </div>
        </td>

        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Área
            </div>
            <div class="cell-value">
                {{ $area }}
            </div>
        </td>
    </tr>
</table>

<p class="margin-top">
    De acuerdo con el presente documento, declaro bajo conformidad haber realizado mi capacitación en el sistema establecido por la empresa - Plataforma digital de
    capacitación, en la fecha registrada habiendo ingresado con mi usuario y contraseña a la página
    {{ $company->appUrl ?? '' }}
</p>

<p>
    <b>
        El curso establecido por la empresa y que he aprobado de manera satisfactoria es:
    </b>
</p>

<table class="table-form">

    <tr class="row">
        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Nombre del curso
            </div>
            <div class="cell-value">
                {{ $course['name'] }}
            </div>
        </td>

        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Horas lectivas
            </div>
            <div class="cell-value">
                {{ $course['duration'] }} horas
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Fecha de capacitación
            </div>
            <div class="cell-value">
                {{ substr($summaryCourse['last_time_evaluated_at'] ?? '', 0, 10) }}
            </div>
        </td>

        <td class="cell" style="width: 50%">
            <div class="cell-label">
                Fecha de registro
            </div>
            <div class="cell-value">
                {{ substr($summaryCourse['last_time_evaluated_at'] ?? '', 0, 10) }}
            </div>
        </td>
    </tr>
</table>

<p>
    <b>
        Los temas o conceptos impartidos han sido los siguientes:
    </b>
</p>

<div class="topics">
{!!  $course['registro_capacitacion']->syllabus !!}
</div>

<p>
    <b>
        OBSERVACIONES:
    </b>
</p>

<div class="comment margin-bottom">
    {{ $course['registro_capacitacion']->comment ?? '' }}
</div>

<table class="table-form margin-bottom">
    <tr>
        <td colspan="2"
            class="cell text-center pt-3"
            style="padding-top: 15px">
            <span>
                <b>
                Suscriben el presente acuerdo ambas partes en señal de conformidad con su contenido y forma.
                </b>
            </span>
        </td>
    </tr>
    <tr>
        <td class="text-center"  style="padding-top: 16px; padding-bottom: 20px">
            <img src='{{ $trainerSignatureData }}' alt=""
                 style="width: auto; height: 80px;" >
            <hr style="width: 50%">
            <span>LA EMPRESA</span>
        </td>
        <td class="text-center"  style="padding-top: 16px; padding-bottom: 20px">
            <img src='{{ $userSignatureData }}' alt=""
                 style="width: auto; height: 80px;" >
            <hr style="width: 50%">
            <span>EL TRABAJADOR</span>
        </td>

    </tr>
</table>


</body>
</html>
