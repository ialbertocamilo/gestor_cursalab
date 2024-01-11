<!DOCTYPE html>
<html>
<head>
    <title>REGISTRO DE CAPACITACIÓN - {{ $course['certificationCourseCode'] }}</title>
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
        height: 21px;
    }

    .topics {
        min-height: 50px;
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

</style>

<h4 class="main-title">
    REGISTRO DE CAPACITACIÓN - {{ $course['certificationCourseCode'] }}
</h4>

<table class="table-form">

    <tr class="title-wrapper">
        <td colspan="12" class="table-title">
            DATOS DE "LA EMPRESA"
        </td>
    </tr>

    <tr class="row">
        <td class="cell" colspan="12">
            <div class="cell-label">
                Razón social
            </div>
            <div class="cell-value">
                {{ $company['businessName'] ?? '' }}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell"  colspan="4">
            <div class="cell-label">
                RUC
            </div>
            <div class="cell-value">
                {{ $company['businessNumber'] ?? '' }}
            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Actividad económica
            </div>
            <div class="cell-value">
                {{ $company['economicActivity'] ?? '' }}
            </div>
        </td>

        <td class="cell"  colspan="2">
            <div class="cell-label">
                Con código CIIU Nº
            </div>
            <div class="cell-value">
                {{ $company['CIIU'] ?? '' }}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell" colspan="12">
            <div class="cell-label">
                Dirección de la sede
            </div>
            <div class="cell-value">
                {{ $company['address'] ?? '' }}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell"  colspan="6">
            <div class="cell-label">
                Número de trabajadores del centro laboral
            </div>
            <div class="cell-value">
                {{ $company['workersCount'] ?? '' }}
            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Capacitador y encargado del registro
            </div>
            <div class="cell-value">
                {{ $company['trainerAndRegistrar'] ?? '' }}
            </div>
        </td>
    </tr>
</table>

<table class="table-form margin-top">

    <tr class="title-wrapper">
        <td colspan="12" class="table-title">
            DATOS DEL “TRABAJADOR
        </td>
    </tr>

    <tr class="row">
        <td class="cell" colspan="6">
            <div class="cell-label">
                Nombre completo
            </div>
            <div class="cell-value">
                {{$user['name']}}
            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Documento de identificación
            </div>
            <div class="cell-value">
                {{$user['document']}}
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell"  colspan="6">
            <div class="cell-label">
                Cargo
            </div>
            <div class="cell-value">

            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Área
            </div>
            <div class="cell-value">

            </div>
        </td>
    </tr>
</table>

<p class="margin-top">
    De acuerdo con el presente documento, declaro bajo conformidad haber realizado mi capacitación en el sistema establecido por la empresa - Plataforma digital de
    capacitación, en la fecha registrada habiendo ingresado con mi usuario y contraseña a la página
    {{ $company['appWebsite'] ?? '' }}
</p>

<p>
    <b>
        El curso establecido por la empresa y que he aprobado de manera satisfactoria es:
    </b>
</p>

<table class="table-form">

    <tr class="row">
        <td class="cell" colspan="6">
            <div class="cell-label">
                Nombre del curso
            </div>
            <div class="cell-value">
                {{ $course['name'] }}
            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Horas lectivas
            </div>
            <div class="cell-value">
                {{ $course['duration'] }} horas
            </div>
        </td>
    </tr>

    <tr class="row">
        <td class="cell"  colspan="6">
            <div class="cell-label">
                Fecha de capacitación
            </div>
            <div class="cell-value">
                {{ $summaryCourse['created_at'] }}
            </div>
        </td>

        <td class="cell"  colspan="6">
            <div class="cell-label">
                Fecha de registro
            </div>
            <div class="cell-value">
                {{ $summaryCourse['created_at'] }}
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
{!!  $course['certificationSyllabus'] !!}
</div>

<p>
    <b>
        OBSERVACIONES:
    </b>
</p>

<div class="comment margin-bottom">
    {{ $course['certificationComment'] }}
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
            <div style="height: 80px;"></div>
            <hr style="width: 50%">
            <span>LA EMPRESA</span>
        </td>
        <td class="text-center"  style="padding-top: 16px; padding-bottom: 20px">
            <img src='data:image/png;base64, {{ $signatureData }}' alt=""
                 style="width: auto; height: 80px;" >
            <hr style="width: 50%">
            <span>EL TRABAJADOR</span>
        </td>

    </tr>
</table>


</body>
</html>
