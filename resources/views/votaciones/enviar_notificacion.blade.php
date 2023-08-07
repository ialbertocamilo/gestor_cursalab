@extends('emails.plantilla_email')
@section('body')
<tr style="background-color:#F9FAFB;margin-top:10px" align="center">
    <td>
        <h1 style=" color:#5458EA;margin-top:2rem;font-weight:bold;font-size: 1.3rem;">¡Haz sido postulado!</h1>
    </td>
</tr>
<tr style="background-color:#F9FAFB;" align="center">
    <td colspan="2" style="padding:0;">
        <div style="margin: 0 4rem;background-color:white;padding: 16px 0px;border-radius: 4px;">
            <p style="color:#333D5D;margin-bottom:1rem;margin-top:3px;font-weight:bold">
            	Campaña : {{ $data['title'] }}
            </p>

            <p style="color:#333D5D;margin-bottom:1rem;margin-top:3px;">
                {{ $data['body'] }}
            </p>
            <p style="color:#333D5D;margin-bottom:0;margin-top:3px;">
                Usuario: {{ $data['user'] }}
            </p>
        </div>
    </td>
</tr>
@endsection
