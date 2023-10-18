@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                Querido {{$data['user']}},
                Nos emociona darte la bienvenida a la familia de Cursalab. Esperamos que tu tiempo con nosotros esté lleno de oportunidades emocionantes, aprendizaje y crecimiento.
                Siempre estamos aquí para ayudarte, así que no dudes en comunicarte si necesitas algo. ¡Estamos ansiosos por trabajar contigo y crear juntos un futuro exitoso!
                ¡Bienvenido a bordo!
            </p>
        </td>
    </tr>
@endsection
