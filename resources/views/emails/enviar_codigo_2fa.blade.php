@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <h1 style=" color:#333D5D;margin-top:2rem;font-weight:bold;font-size: 1.1rem; padding: 0px 46px;">¡Hola!</h1>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                <b>{{ $data['user'] }}</b>, tu código de verificación es:
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" style="padding:0;">
            <div style="margin: 0 46px;background-color:#F9FAFB;padding: 16px 0px;border-radius: 4px;">
                <p style="color:#333D5D; text-align:center; font-size: 1.5rem; margin-bottom:3px;margin-top:3px;">
                    {{ $data['code'] }}
                </p>
            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">Ten en cuenta que este código expirará en {{ $data['minutes'] }} minutos.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">El equipo Cursalab.
            </p>
        </td>
    </tr>
@endsection
