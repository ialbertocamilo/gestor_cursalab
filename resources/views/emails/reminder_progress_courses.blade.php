@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;">
        <td align="center">
            <img style="width: 250px" src="{{ url('/images/fast-working-flatline.png') }}" alt="">
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; " align="center">
            @php
                $message = count($data['courses']) > 0 ? 'tus cursos' : 'tu curso';
            @endphp
            <p style="color:#333D5D;font-size: 1.2rem;padding: 0px 46px;">
                Te quedaste a medio camino, completa {{$message}}
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td style="font-family: 'Poppins', sans-serif;">
            <h1
                style="text-align:justify; color:#2A3649; margin-top:2rem;font-weight:bold;font-size: 25px; padding: 0px 46px;">
                <div
                    style="margin-left: 95%; 
                          width:25px; 
                          height:5px; 
                          border-radius:4px; 
                          background: #FFCD0C;">
                </div>
                @if (count($data['courses'])>1)
                    <ul style="margin: 0;padding: 10px 0">
                        @foreach ($data['courses'] as $course)
                            <li style="margin: 0;padding: 0"><span>{{ $course['name'] }}</span></li>
                            <br>
                        @endforeach
                    </ul>
                @else    
                    @foreach ($data['courses'] as $course)
                        <span>{{ $course['name'] }}</span>
                        <br><br>
                    @endforeach
                @endif
                <div
                    style="margin-left: 8%;
                          width: 9px; 
                          height: 9px; 
                          border-radius: 9px; 
                          background: #CC96FC;">
                </div>
            </h1>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td style="font-family: 'Poppins', sans-serif;">
            <h1
                style="text-align:center; color:#5457E7; margin-top:2rem;font-weight:bold;font-size: 25px; padding: 0px 46px;">

                <div
                    style="margin-left: 95%;
                width: 9px; 
                height: 9px; 
                border-radius: 9px; 
                background: #CC96FC;">
                </div>
                <a target="_BLANK" href="{{$data['web_url']}}">¡Ingresa aquí!</a>
                <div style="margin-left: 8%; 
                width:25px; 
                height:5px; 
                border-radius:4px; 
                background: #FFCD0C;"></div>
            </h1>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:justify; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                <span style="font-weight:bold;">¿No puedes ingresar a la plataforma?</span> Contáctate inmediatamente con el
                equipo de <b style="color: #5457E7;">Customer Success.</b><br>Este es un mensaje automático, por favor no
                responder.
            </p>
        </td>
    </tr>
@endsection
