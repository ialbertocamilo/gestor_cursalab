@extends('emails.plantilla_email')
@section('body')
<tr style="background-color:#F9FAFB;margin-top:10px" align="center">
    <td>
        <h1 style=" color:#5458EA;margin-top:2rem;font-weight:bold;font-size: 1.3rem;">¡Te han invitado a Cursalab!</h1>
    </td>
</tr>
<tr style="background-color:#F9FAFB;">
    <td colspan="2" style="padding:0;">
        <div style="margin: 0 4rem;background-color:white;padding: 16px 20px;border-radius: 4px;">
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                {{$data['user']->name}} desea que puedas ingresar a tu nueva plataforma de capacitación. 
            </p>
            <br>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                Ahora podrás acceder a tus cursos y disfrutar de los siguientes beneficios:
            </p>
            <br>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">1. Rinde tus cursos en cualquier hora y lugar y revísalos las veces que quieras.</p>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">2. Accede a tus certificados descargables</p>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">3. Mira las últimas noticias de tu organización en la sección de Anuncios.</p>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">4. Revisa tu progreso general y por cursos.</p> 
            <br>
        </div>
    </td>
</tr>
<tr style="background-color:#F9FAFB;">
    <td colspan="2" align="center" style="padding: 10px 0px;">
        <a target="_blank" href="{{$data['enlace']}}" style="font-size:0.8rem;background-color: #73C0E6;border-radius: 10px;padding: 10px 30px;text-decoration: none;color: white;transition: all 0.5s ease-in-out;">IR AHORA</a>
    </td>
</tr>
{{-- <tr style="background-color:#F9FAFB;">
    <td>
        <p style="color:#333D5D;text-align:center;font-size: 0.85rem;margin-bottom: 1rem;">Para acceder desde tu celular, descarga nuestra app en 
            <a target="_blank" href="{{config('app.APP_PLAYSTORE')}}">Play Store</a> 
            o <a target="_blank" href="{{config('app.APP_GALERY')}}">App Galery</a> </p>
    </td>
</tr> --}}
@endsection