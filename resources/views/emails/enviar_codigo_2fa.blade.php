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
              
              Código de verificación
              
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
                
                <p style="color:#120C29; text-align:center; font-size: 2rem; margin-bottom:3px;margin-top:3px; font-weight:bold; letter-spacing:7px">
                        {{ $data['code'] }}
                </p>
                
                <div style="margin-left:auto;margin-right: -3rem;width: 9px; height: 9px; border-radius: 9px; background: #CC96FC;"></div> 

            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">El código de verificación será válido por {{ $data['minutes'] }} minutos. Por favor no compartas este código con nadie.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            ¿No reconoces esta actividad?. Por favor contáctate inmediatamente con el equipo de Soporte.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
            Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>
@endsection
