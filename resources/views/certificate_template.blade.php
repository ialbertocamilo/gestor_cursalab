<?php
    setlocale(LC_TIME, 'es_PE.UTF-8');

    $sp_slug = 'sanpablo';
    $css_file = $config->is_v1_migrated ? 'certi-v1.css' : 'certi.css';
    $user_class = config('app.customer.slug') != $sp_slug ? 'nombre' : 'nombre-sp';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">

    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv="Expires" content="-1">
    <meta http-equiv='pragma' content='no-cache'>

    <title>Certificado</title>

    <link type="text/css" media="all" rel="stylesheet" href="{{ asset('css/' . $css_file) }}">

    <meta property="og:type" content="website" />
    <meta property="og:title" content="Cursalab, LMS Corporativa multiplataforma" />
    <meta property="og:description" content="Capacitación, evaluación, monitoreo y analítica en un solo lugar" />
    <meta property="og:image" content="https://sfo2.digitaloceanspaces.com/clb1/images/ambiente/Compartir%20Diploma-02.jpg" />
    <meta property="og:image:width" content="828" />
    <meta property="og:image:height" content="450" />
    <meta property="og:url" content="https://cursalab.io" />
    <meta property="og:site_name" content="Cursalab" />
</head>
<body>

    <div class="container" id="certi" download="canvasexport.png">
        
        @if($data['old_template'] === true)

            <img src="{{ $data['image'] }}" alt="Certificado">
            <span class="{{ $user_class }} default">{{ $data['users'] }}</span>
            
            @if(config('app.customer.slug') != $sp_slug)
                <span class="curso default">{{ $data['courses'] }}</span>
            @endif
        
            @if ($data['show_certification_date'])
                <span class="fecha">{{ fechaCastellano($data['fecha']) }}</span>
            @endif

        @else

            {{-- position: absolute; --}}
            <img style="z-index: 0; top: 0; left: 0; max-height: 100%; max-width: 100%;"
                 src="{{ $data['image'] }}"
                 alt="Certificado">

        @endif

    </div>
</body>

@if($download)

    <script src="{{ asset('js/canvas/html2canvas.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
    <script>
        function screenshot() {
            let file_name = "{{ $data['courses'] ?? 'Cursalab' }}";
            file_name = file_name.replace(/\s/g, '-').toLowerCase();
        
            setTimeout(function () {
                window.scrollTo(0,0)
                var html2Obj = html2canvas(certi, { width: certi.offsetWidth, height: certi.offsetHeight }).then(function(canvas) {
                    const a = document.createElement("a");
                    document.body.appendChild(a);
                    a.href = canvas.toDataURL();
                    a.download = "certificado-"+file_name+".png";
                    a.click();
                });
            }, 1000);
        }

        window.onload = function () {

            setTimeout(function () {
                screenshot();
            }, 1000);
        };
    </script>

@endif

</html>
