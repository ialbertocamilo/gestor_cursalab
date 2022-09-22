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
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
        }
    </style>
    <link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
</head>

<body style="font-family:Roboto;margin:0;padding:0;word-spacing:normal;background-color:#F8F9FC;">
    <div role="article" aria-roledescription="email" lang="es"
        style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#F8F9FC;">
        <table role="presentation" style="width:100%;border:none;border-spacing:0;">
            <tr>
                <td align="center" style="padding:0;">
                    <table role="presentation"
                        style="width:98%;max-width:600px;border:none;border-spacing:0;text-align:left;font-family: 'Roboto', sans-serif;font-size:16px;line-height:22px;color:#363636;border:1px solid #e6e7e8;padding:30px;background:white;">
                        {{-- HEADER --}}
                        <tr>
                            <td colspan="2" align="center">
                                <table role="presentation"
                                    style="width:100%;border:none;border-spacing:0;border-top-left-radius: 4px; border-top-right-radius: 4px;background-color: #5458EA;">
                                    <tr>
                                        <td style="padding-left: 15px;">
                                            <a href="https://cursalab.io/" target="_blank"
                                                style="color: white;text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/cursalab.png"
                                                    alt="Cursalab" width="130"
                                                    style="max-width: 130;height:auto;border:none;">
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        {{-- BODY --}}
                        @yield('body')
                        {{-- FOOTER --}}
                        <tr style="">
                            <td colspan="2" align="center">
                                <table role="presentation"
                                    style="text-align:center;width:100%;border:none;border-spacing:0;padding:15px 0 10px;border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;background-color: #5458EA;">
                                    <tr align="center" style="width: 60%;display: inline-table;">
                                        <td>
                                            <a target="_blank"
                                                href="https://www.linkedin.com/company/cursalabpe?utm_source=Mailing&utm_id=LinkedIn">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-li.png?v=0.1"
                                                    alt="Cursalab" style="max-width: 100%;" width="20px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.facebook.com/CursalabIO?utm_source=Mailing&utm_id=Facebook"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-fb.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="20px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.instagram.com/cursalab.io?utm_source=Mailing&utm_id=Instagram"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-in.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://www.youtube.com/channel/UCLd85njz2WdqM9Bm2XBNJoA?utm_source=Mailing&utm_id=YouTube"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-yt.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://open.spotify.com/show/4Bdd78oYHyZHqwaHopgQEd?si=cfd7e2086d3d4a1f"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-spotify.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="22px">
                                            </a>
                                        </td>
                                        <td>
                                            <a target="_blank"
                                                href="https://vm.tiktok.com/ZMRCwrbHV?utm_source=Mailing&utm_id=TikTok"
                                                style="text-decoration: none;">
                                                <img src="https://cursalab.io/mailing/21-09-2021/img/icon-tik.png"
                                                    alt="Cursalab" style="max-width: 100%;" width="18px">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="https://api.whatsapp.com/send?phone=51964156106&text=HolaCursalab,estoyinteresadoensusserviciosdecapacitaci%C3%B3ndigital"
                                                target="_blank" style="text-decoration: none;">
                                                <img src="{{ url('/img/icon-whatsapp.png') }}" alt=""
                                                    width="20" style="max-width: 20;height:auto;border:none;">
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" colspan="12">
                                <p style="color:#5D5FEF;font-size: 13px;font-weight: bold;">© Cursalab
                                    {{ date('Y') }} | Lima, Perú</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
