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

                                                @if(isset($data) && isset($data['image_logo']))
                                                    <img
                                                        {{-- src="https://cursalab.io/wp-content/uploads/2022/11/logo-web-light-1536x506.png" --}}
                                                        src="{{ $data['image_logo'] }}"
                                                        alt="Cursalab" width="150"
                                                        style="max-width: 150;height:auto;border:none;">
                                                @else
                                                    <img
                                                        {{-- src="https://cursalab.io/wp-content/uploads/2022/11/logo-web-light-1536x506.png" --}}
                                                        src="{{ url('/img/logo_cursalab_v2_black.png') }}"
                                                        alt="Cursalab" width="150"
                                                        style="max-width: 150;height:auto;border:none;">
                                                @endif
                                            </a>
                                        </td>
                                        @if(isset($data['image_subworkspace']))
                                            <td style="text-align: end">
                                                <a href="https://cursalab.io/" target="_blank"
                                                    style="color: white;text-decoration: none;">
                                                    <img
                                                        src="{{ $data['image_subworkspace'] }}"
                                                        alt="Cursalab" width="120"
                                                        style="max-width: 150;height:auto;border:none;">
                                                </a>
                                            </td>
                                        @endif
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
