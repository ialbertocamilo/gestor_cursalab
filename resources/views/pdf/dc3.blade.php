<!DOCTYPE html>
<html>
<head>
    <title>Prueba</title>
</head>
<body>
    <h4 style="text-align: center;font-weight: bolder;margin:0">FORMATO DC-3</h4>
    <h4 style="text-align: center;font-weight: bolder;margin:0">CONSTANCIA DE COMPETENCIAS O DE HABILIDADES LABORALES</h4>
    <div style="border: 2px solid black;">
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align: center;background: black;color:white;padding:8px 0px 8px 0px;">
                <td colspan="2">DATOS DEL TRABAJADOR</td> 
            </tr> 
            <tr>
                <td style="padding: 5px 5px 10px 5px;border-top: 2px solid black;" colspan="2">
                    <div>Nombre (Anotar apellido paterno, apellido materno y nombre(s))</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">Pedro Guerrero Matute</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 5px 0px 5px; border-top: 2px solid black;">
                    <div>Clave Única de Registro de Población</div>
                    @php
                        $number = str_split('145L0789asd');
                    @endphp
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            @foreach ($number as $n)
                                <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                            @endforeach
                        </tr>
                    </table>
                </td>
                <td style="padding: 5px 5px 10px 5px;border-top: 2px solid black;border-left: 2px solid black">
                    <div>Ocupación especifica (Catálogo Nacional de Ocupaciones)</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">09</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 5px 5px 10px 5px;border-top: 2px solid black;" colspan="2">
                    <div>Puesto</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">Terapeuta</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div style="border: 2px solid black;">
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align: center;background: black;color:white;padding:8px 0px 8px 0px;">
                <td colspan="2">DATOS DE LA EMPRESA</td> 
            </tr> 
            <tr>
                <td style="padding: 5px 5px 10px 5px;border-top: 2px solid black;" colspan="2">
                    <div>Nombre o razón social (En caso de persona fisica, anotar apellido paterno, apellido materno y nombre(s))</div>
                    <table style="width:100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-weight: bolder">Instituto Mexicano de Formación Interdisciplinario A.C</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <td style="padding: 5px 5px 0px 5px; border-top: 2px solid black;" colspan="2">
                <div>Registro Federal de Contribuyentes con homoclave (SHCP)</div>
                @php
                    $number = str_split('145L0789asd');
                @endphp
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        @foreach ($number as $n)
                            <td style="border-right: 2px solid black; padding: 0px 10px 10px 10px;font-weight: bolder">{{$n}}</td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </table>
    </div>
</body>
</html>
