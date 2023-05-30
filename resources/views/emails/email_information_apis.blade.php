@extends('emails.plantilla_email')
@section('body')
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                Descarga el reporte diario de datos de usuarios que han sido actualizados de manera automática.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB;margin-top:10px">
        <td style="font-family: 'Poppins', sans-serif;">
            <h1 style="text-align:center; color:#2A3649; margin-top:2rem;font-weight:bolder;font-size: 25px; padding: 0px 46px;">
                <div style="margin-left: 95%; 
                          width:25px; 
                          height:5px; 
                          border-radius:4px; 
                          background: #FFCD0C;"></div>
              Reporte de actualización <br>en datos
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
                
                <p style="color:#120C29; text-align:center; font-size: 1.5rem; margin-bottom:3px;margin-top:3px; font-weight:bold;">
                       <a href="#">Descargar reporte</a>
                </p>
                
                <div style="margin-left:auto;margin-right: -3rem;width: 9px; height: 9px; border-radius: 9px; background: #CC96FC;"></div> 

            </div>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
                Los datos mostrados fueron recopilados del 16/05/23 a las 6 hrs hasta el 17/05/23 a las 6 hrs.
            </p>
        </td>
    </tr>
    <tr style="background-color:#F9FAFB; text-align:center; margin-top:10px;">
        <td style="font-family: 'Poppins', sans-serif; ">
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">
            ¿No reconoces esta actividad? Contáctate inmediatamente con el equipo de Customer Experience.
            </p>
            <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px; margin-bottom:25px">
            Este es un mensaje automático, por favor no responder.
            </p>
        </td>
    </tr>
@endsection
