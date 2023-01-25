@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <h1 style=" color:#333D5D;margin-top:2rem;font-weight:bold;font-size: 1.1rem; padding: 0px 46px;">¡Hola!</h1>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                {{ $data['user'] }}, tu código de verificación es:
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
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">El código de verificación sera válido por {{ $data['minutes'] }} minutos. Por favor no compartas este código con nadie.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            ¿No reconoces esta actividad?. Por favor contáctate inmediatamente con el equipo de Soporte de Cursalab.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <p style="color:#333D5D; font-style: italic; font-size: .9rem;padding: 0px 46px;">Este mensaje es automático, por favor no responder.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">Cursalab.
            </p>
        </td>
    </tr>
@endsection
