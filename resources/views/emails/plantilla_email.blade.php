<!DOCTYPE html>
<html lang="es" xmlns="https://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Soporte - Cursalab</title>
    <style>
        table,
        td,
        div,
        h1,
        p {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
</head>

<body style="font-family: 'Poppins', sans-serif; margin:0; padding:0; word-spacing:normal; background-color:#F8F9FC;">

  
    <div role="article" aria-roledescription="email" lang="es"
        style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#F8F9FC;">
        <table role="presentation" style="width:100%;border:none;border-spacing:0;">
            <tr>
                <td align="center" style="padding:0;">
                    <table role="presentation"
                        style="width:98%;max-width:600px;border:none;border-spacing:0;text-align:left;font-size:16px;line-height:22px;color:#363636;border:1px solid #e6e7e8;padding:30px;background:white; position:relative">
                        {{-- HEADER --}}
                        <tr>
                            <td colspan="2" align="center" style="font-family: 'Poppins', sans-serif;">
                                <table role="presentation"
                                    style="width:100%;border:none;border-spacing:0;border-top-left-radius: 4px; border-top-right-radius: 4px;background-color: #FFFFFF; padding: 0;">
                                    <tr >
                                        <td >
                                            <a href="https://cursalab.io/" target="_blank"
                                                style="color: white;text-decoration: none;">
                                                <img 
                                                    {{-- src="https://cursalab.io/wp-content/uploads/2022/11/logo-web-light-1536x506.png" --}}
                                                    src="/img/logo_cursalab_v2_black.png"
                                                    alt="Cursalab" width="150"
                                                    style="max-width: 150;height:auto;border:none;">
                                            </a>
                                        </td> 
                                      <td style="font-family: 'Poppins', sans-serif; text-align: end; font-size:.8rem; font-weight: 600;">
                                      </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        {{-- HEADER --}}

                        {{-- BODY --}}
                        @yield('body')
                        {{-- BODY --}}

                        {{-- FOOTER --}}
                        <tr>
                            <td colspan="2" align="center">
                                <table role="presentation"
                                    style="text-align:center;width:100%;border:none;border-spacing:0;padding:15px 0 10px;border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;background-color: #5458EA;">
                                    <tr align="center" style="width: 60%;display: inline-table;">
                                        <td>
                                            <a target="_blank"
                                                href="https://www.linkedin.com/company/cursalabpe?utm_source=Mailing&utm_id=LinkedIn">
                                                <img src="{{ url('/img/linkedin.png') }}"
                                                    alt="Cursalab" style="max-width: 100%;" width="20px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.facebook.com/CursalabIO?utm_source=Mailing&utm_id=Facebook"
                                                style="text-decoration: none;">
                                                <img 
                                                src="{{ url('/img/facebook.png') }}"    
                                                    alt="Cursalab" style="max-width: 100%;" width="20px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.instagram.com/cursalab.io?utm_source=Mailing&utm_id=Instagram"
                                                style="text-decoration: none;">
                                                <img src="{{ url('/img/instagram-icon.png') }}"
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.youtube.com/channel/UCLd85njz2WdqM9Bm2XBNJoA?utm_source=Mailing&utm_id=YouTube"
                                                style="text-decoration: none;">
                                                <img 
                                                src="{{ url('/img/youtube.png') }}"   
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://open.spotify.com/show/4Bdd78oYHyZHqwaHopgQEd?si=cfd7e2086d3d4a1f"
                                                style="text-decoration: none;">
                                                <img 
                                                    src="{{ url('/img/icon-spotify.png') }}"   
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        {{-- <td>
                                            <a target="_blank"
                                                href="https://www.tiktok.com/@cursalab.io"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-tik.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="18px">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="https://api.whatsapp.com/send?phone=51964156106&text=Hola Cursalab, estoy interesado en sus servicios de capacitaci%C3%B3n digital"
                                                target="_blank" style="text-decoration: none;">
                                                <img src="{{ url('/img/whatsapp-icon.png') }}" alt=""
                                                    width="22px" style="max-width: 100%;">
                                            </a>
                                        </td>              --}}                       
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" colspan="12"  style="font-family: 'Poppins', sans-serif;">
                                <p style="color:#5D5FEF;font-size: 13px;font-weight: bold;">© Cursalab
                                    {{ date('Y') }} | Worldwide</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
