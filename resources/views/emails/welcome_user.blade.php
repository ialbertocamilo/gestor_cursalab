@extends('emails.plantilla_email')
@section('body')
<tr style="background-color:#F9FAFB;margin-top:10px" align="center">
    <td>
        <h1 style=" color:#5458EA;margin-top:2rem;font-weight:bold;font-size: 1.3rem;">¡Cursalab y {{$data['subworkspace_name']}} te dan la bienvenida!</h1>
        {{-- <p style="color:#333D5D;font-size: .9rem;padding: 0px 46px;">¡Accede ya mismo a {{ (env('MULTIMARCA') && env('MARCA')!='agile') ? 'Cursalab' : $config_general->titulo }}, empieza a aprender y continúa tu desarrollo!</p> --}}
    </td>
</tr>
<tr style="background-color:#F9FAFB;" align="center">
    <td colspan="2" style="padding:0;">
        <div style="margin: 0 4rem;background-color:white;padding: 16px 0px;border-radius: 4px;">
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;font-weight:bold">
                {{$data['name'].' '.$data['lastname']}}
            </p>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                Usuario: {{$data['document']}}
            </p>
        </div>
    </td>
</tr>
<tr style="background-color:#F9FAFB;">
    <td colspan="2" align="center" style="padding: 10px 0px;">
        <a target="_blank" href="{{$data['web_url']}}" style="font-size:0.8rem;background-color: #73C0E6;border-radius: 10px;padding: 10px 30px;text-decoration: none;color: white;transition: all 0.5s ease-in-out;">IR AHORA</a>
    </td>
</tr>
@endsection