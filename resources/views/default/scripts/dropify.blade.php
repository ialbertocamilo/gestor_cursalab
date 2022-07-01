@push('css')
    <link type="text/css" rel="stylesheet" href="{{ asset('admin/plugins/dropify/dist/css/dropify.min.css') }}" />
@endpush

@push('libraries')
    <script type="text/javascript" src="{{ asset('admin/plugins/dropify/dist/js/dropify.min.js') }}"></script>
@endpush

@push('scripts')

<script type="text/javascript"> 
        
    initDropify();
    // console.log('INIT');

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

<script type="text/javascript">

    var drEvent = $('.dropify').dropify();

    var hideConfirmBox = false;

    // var content_media_url = "route('admin.contents.media.destroy', [':CONTENT', ':MEDIA']) }}";
    var content_media_url = "";
   
    drEvent.on('dropify.beforeClear', function(event, element){
        var media_id = $(element.element).data('media-id')
        let e_delete = 'e_delete_'+element.element.attributes.name.value; 
        console.log(e_delete);
        $('input[name ="'+e_delete+'"]').val(1); 
        if ( media_id )
        {
            if (hideConfirmBox == false) {
                swal({
                    title: '¿Desea eliminar este archivo?',
                    text: 'Esta acción no se puede deshacer.',
                    buttons: {
                      cancel: true,
                      confirm: {
                        text: '¡Sí, eliminar!',
                        value: 'proceed'
                      }
                    },
                    dangerMode: true
                }).then((value) => {
                    if (value == 'proceed') {
                        $("body").trigger("click")
                        hideConfirmBox = true;
                        $(this).next('button.dropify-clear').trigger('click');
                    }
                });
            }

            return hideConfirmBox;
        }
    });

    drEvent.on('dropify.afterClear', function(event, element){
        console.log(event);
        var content_id = $(element.element).data('content-id')
        var media_id = $(element.element).data('media-id')
        var url = content_media_url.replace(":CONTENT", content_id).replace(":MEDIA", media_id)
        
        if ( media_id )
        {
            hideConfirmBox = false;

            $.post( url, function(data, textStatus, xhr) {

                if (data.status == 'success'){
                    swal(data.message, {
                        icon: "success",
                    });

                    $(element.element).data('media-id', '')

                }else{
                    swal(data.message, {
                        icon: "error",
                    });
                }
            });
        }
    });
</script>

@endpush