@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td>
            <h1 style="text-align:center; color:#2A3649; margin-top:2rem;font-weight:bold;font-size: 25px; padding: 0px 46px;">
                <div style="margin-left: 95%; 
                        width:25px; 
                        height:5px; 
                        border-radius:4px; 
                        background: #FFCD0C;"></div>
                @if (isset($data['data_custom']['title']))
                    {{ $data['data_custom']['title'] }}
                @else
                    Bienvenido a Cursalab              
                @endif
                <div style="margin-left: 8%;
                            width: 9px; 
                            height: 9px; 
                            border-radius: 9px; 
                            background: #CC96FC;"></div>       
            </h1>
            <div>
                @if (isset($data['data_custom']['content']))
                    <div style="color:#333D5D;padding: 0px 46px;">{!! $data['data_custom']['content'] !!}</div>
                @else
                    <div style="color:#333D5D;font-size: .9rem;padding: 0px 46px;text-align: center">
                        Nos emociona darte la bienvenida a la familia de Cursalab. Esperamos que tu tiempo con nosotros esté lleno de oportunidades emocionantes, aprendizaje y crecimiento.
                        <span style="font-weight: bolder">¡Bienvenido a bordo!</span>
                    </div>
                    <div style="color:#333D5D;font-size: .9rem;padding: 0px 46px;text-align: center;margin-top:12px">
                        Recuerda que tu ingreso a la plataforma se realizará con tu <span style="font-weight: bolder">número de documento</span> como usuario y contraseña.
                    </div>
                @endif
            </div>
            <div style="text-align: center;margin-top:30px">
                <div style="margin-left: 95%;
                width: 9px; 
                height: 9px; 
                border-radius: 9px; 
                background: #CC96FC;"></div> 
                <a target="_blank"
                    href="https://play.google.com/store/apps/details?id=io.cursalab.app"
                    style="text-decoration: none;margin-right:10px">
                    <img 
                        src="{{ url('/img/playstore.png') }}"   
                        alt="Cursalab plastore" style="max-width: 100%;" width="50px">
                </a>
                <a target="_blank"
                    href="https://apps.apple.com/pe/app/cursalab/id6454143761"
                    style="text-decoration: none;margin-left:10px">
                    <img 
                        src="{{ url('/img/appstore.png') }}"   
                        alt="Cursalab appstore" style="max-width: 100%;" width="50px">
                </a>
                <div style="margin-left: 8%; 
                        width:25px; 
                        height:5px; 
                        border-radius:4px; 
                        background: #FFCD0C;"></div>
                      
            </div>
            <div style="">
                <h1 style="text-align:center; color:#2A3649; margin-top:0;font-weight:bold;font-size: 1rem; padding: 0px ;">
                    o
                </h1>
            </div>
            <div style="text-align: center;margin-bottom:20px">
                <a target="_blank"
                    href="https://app.cursalab.io/"
                    style="
                        background: #5457E7;
                        color: #FFF;
                        text-align: center;
                        font-family: Poppins;
                        font-size: 1rem;
                        font-style: normal;
                        font-weight: 400;
                        line-height: 20px; 
                        letter-spacing: 0.1px;
                        text-decoration: none;
                        padding:4px 20px;
                        border-radius: 10px;
                    ">
                    Ingresar desde la web
                </a>
            </div>
            <div>
                <div style="color:#333D5D;font-size: .9rem;padding: 0px 46px;text-align:center">
                    <span style="font-weight: bolder">¿Tienes problemas para ingresar?</span> Contáctate con nuestro equipo de Customer Success.
                </div>
                <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px;text-align:center">
                Este es un mensaje automático, por favor no responder.
                </p>
            </div>
        </td>
    </tr>
@endsection
