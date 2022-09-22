@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <h1 style=" color:#333D5D;margin-top:2rem;font-weight:bold;font-size: 1.1rem; padding: 0px 46px;">¡Hola!</h1>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">Un usuario quiere contactarse contigo.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" style="padding:0;">
            <div style="margin: 0 46px;background-color:#F9FAFB;padding: 16px 0px;border-radius: 4px;">
                <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                    <b>Empresa:</b> {{ $usuario['empresa'] }}
                </p>
                <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                    <b>Nombre:</b> {{ $usuario['nombre'] }}
                </p>
                <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                    <b>DNI:</b> {{ $usuario['dni'] }}
                </p>
                <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                    <b>Telf:</b> {{ $usuario['telefono'] }}
                </p>
                <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                    <b>Detalle:</b> {{ $usuario['detalle'] }}
                </p>
            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" align="center" style="padding: 10px 0px;">
            <a target="_blank" href="{{ url('') }}"
                style="font-size:0.8rem;background-color: #5D5FEF;border-radius: 10px;padding: 10px 30px;text-decoration: none;color: white;transition: all 0.5s ease-in-out;">Ir
                al gestor</a>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">El equipo Cursalab.
            </p>
        </td>
    </tr>
@endsection
