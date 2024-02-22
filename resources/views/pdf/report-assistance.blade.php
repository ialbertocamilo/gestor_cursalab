<!DOCTYPE html>
<html>
<head>
    <title>Listado de usuarios - {{$name_session}}</title>
</head>
<body>
    <!DOCTYPE html>
<html>
<head>
    <title>
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
    .title-wrapper-header {
        text-align: center; background: #d4ced0;
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
    .main-subtitle{
        padding-top:15px;
        padding-bottom: 15px; 
    }
</style>
<h4 class="main-title">
   PARTICIPANTES DE LA SESIÓN
</h4>

<table class="table-form">
    <tr class="title-wrapper">
        <td colspan="12" class="table-title">
            DATOS DE LA SESIÓN
        </td>
    </tr>
    <tr class="row">
        <td class="cell" colspan="12">
            <div class="cell-label">
                Nombre de la sesión
            </div>
            <div class="cell-value">
                {{$name_session}}
            </div>
        </td>
    </tr>
    <tr class="row">
        <td class="cell" colspan="6">
            <div class="cell-label">
                Fecha
            </div>
            <div class="cell-value">
                {{$datetime}}
            </div>
        </td>
        <td class="cell" colspan="6">
            <div class="cell-label">
                N° de horas
            </div>
            <div class="cell-value">
                {{$duration}}
            </div>
        </td>
    </tr>
    <tr class="row">
        <td class="cell" colspan="12">
            <div class="cell-label">
                Nombre del Capacitador
            </div>
            <div class="cell-value">
                {{$host}}
            </div>
        </td>
    </tr>
</table>
<div class="main-subtitle">
    <b>Listado de participantes de la capacitación</b>
</div>
<table class="table-form">
    <tr class="title-wrapper-header">
        <td colspan="{{$colspan}}" class="cell">
            Apellidos y nombres de los capacitados  
        </td>
        <td colspan="{{$colspan}}" class="cell">
            Doc de identidad
        </td>
        <td colspan="{{$colspan}}" class="cell">
            Estado
        </td>
        @if ($required_signature)
            <td colspan="{{$colspan}}" class="cell">
                Firma
            </td>
        @endif
    </tr>
    @foreach ($users as $user)
        <tr class="row">
            <td colspan="{{$colspan}}" class="cell">
                {{$user['fullname']}}
            </td>
            <td colspan="{{$colspan}}" class="cell">
                {{$user['document']}}
            </td>
            <td colspan="{{$colspan}}" class="cell">
                {{$user['status_name'] ?? 'No asistió'}}
            </td>
            @if ($required_signature)
                <td colspan="{{$colspan}}" class="cell" align="center">
                    <img src='{{$user['signature']}}' alt=""
                        style="width: auto; height: 80px;" >
                </td>
            @endif
        </tr>
    @endforeach
</table>
</body>
</html>