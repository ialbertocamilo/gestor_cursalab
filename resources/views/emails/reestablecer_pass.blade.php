@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td style="font-family: 'Poppins', sans-serif;">
            <h1 style="text-align:center; color:#2A3649; margin-top:2rem;font-weight:bold;font-size: 25px; padding: 0px 46px;">
                <div style="margin-left: 95%; 
                          width:25px; 
                          height:5px; 
                          border-radius:4px; 
                          background: #FFCD0C;"></div>
              
              Link de verificación
              
              <div style="margin-left: 8%;
                          width: 9px; 
                          height: 9px; 
                          border-radius: 9px; 
                          background: #CC96FC;"></div>       
          </h1>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;">
        <td colspan="2" style="font-family: 'Poppins', sans-serif; padding:0; ">
            <div style="margin: 10px 95px;background-color:#FFFFFF;padding: 20px 0px;border-radius: 1rem;">
            
                <div style="width:25px; height:5px; margin-left:-4rem; border-radius:4px; background: #FFCD0C;"></div>
                
                <p style="text-align:center; font-size: 1.5rem; margin-bottom:3px;margin-top:3px; font-weight:bold; letter-spacing:2px">
                        <a style="color:#5457E7;" href="{{ $data['link_recovery'] }}" target="_blank"> Haz click aquí </a>
                </p>
                
                <div style="margin-left:auto;margin-right: -3rem;width: 9px; height: 9px; border-radius: 9px; background: #CC96FC;"></div> 

            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; font-weight: bold;">
            	¡Hola! {{ $data['user'] }} Para terminar el proceso de ingreso tendrás que actualizar tu contraseña.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            	El enlace de verificación será válido por {{ $data['time'] }} minutos. Por favor no compartas este enlace con nadie.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            	¿No reconoces esta actividad?. Por favor comunícate inmediatamente con tu 
            	<a href="{{ $data['link_coordinador'] }}" style="color:#5457E7;" target="_blank"> coordinador.</a>
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
            	Este es un mensaje automático por favor no responder.
            </p>
        </td>
    </tr>
@endsection
