@section('js')

<link type="text/css" rel="stylesheet" href="{{ asset('vendor/dropify/dist/css/dropify.min.css') }}" />

<script type="text/javascript" src="{{ asset('vendor/dropify/dist/js/dropify.min.js') }}"></script>

<script type="text/javascript"> 
        
    initDropify();

  function initDropify(){
      $(".dropify").dropify({
            messages: {
                'default': 'Arrastre y suelte un archivo aquí o haga clic aquí',
                'replace': 'Arrastre y suelte o haga clic para reemplazar',
                'remove':  'Eliminar',
                'error':   'Vaya, algo malo pasó.'
            }
        });
  }
  
</script>

@endsection