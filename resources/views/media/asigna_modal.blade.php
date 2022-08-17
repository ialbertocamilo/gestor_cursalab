<div class="modal fade media_modal" id="AsignMediaModal" tabindex="-1" role="dialog" aria-labelledby="AsignMediaModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
	  <div class="modal-content">
		<div class="modal-header">
		  <h5 class="modal-title" id="AsignMediaModalLabel">Seleccionar multimedia</h5>
		  <div class="box_busca">
			  <div class="input-group">
				  <input type="search" id="asg_q" class="form-control" placeholder="Buscar">
				  <div class="input-group-append">
					  <button class="btn btn-secondary" type="button" id="asg_btn_busca"><i class="fa fa-search"></i></button>
				  </div>
			  </div>
		  </div>
		  <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		  </button> -->
		</div>
		<div class="modal-body">
		  <div class="container-fluid">
			  
			  <!-- Media init -->
			  <div class="lista_media asg_lista loading" id="asg_med_list">
				  <div class="row loading"></div>
			  </div>
			  <!-- Media fin -->
			  
			  <!-- Media selected  -->
			  <div class="row mt-2">
				  <!-- paginacion -->
				  <div class="col-md-4">
					  <div class="p-1" id="asg_med_pag">
					  </div>
				  </div>
				  <!-- pagination fin -->
			  </div>
			  <!-- Media selected fin -->
  
			  	<input class="form-control" type="hidden" id="asg_field">
			  	<input class="form-control" type="hidden" id="asg_img">
				<input class="form-control" type="hidden" id="asg_filename" >
				<input class="form-control" type="hidden" id="asg_id" >

			  <div class="asg_msg_valida p-1 mt-3 bg-warning text-white"></div>
		  </div>
		</div>
		<div class="modal-footer mt-2 justify-content-between">
		  <div>
			  <button type="button" class="btn btn-default asg_media_refresh"><i class="fas fa-redo-alt"></i> Recargar lista</button>
		  </div>
		   <div>
			  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			  <button type="button" class="btn btn-success" id="asg_save">Guardar</button>
		  </div>
		</div>
	  </div>
	</div>
  </div>
  <script src="/js/pagination_media_modal.js"></script>
  <script>
  	$(document).ready(function() {
            // SelectMediaModal
			$('#AsignMediaModal').on('show.bs.modal', function (event) {
                var element = $(event.relatedTarget);
                var field = element.data('field')
                var tipo = element.data('tipo')
                
				var modal = $(this)
				modal.find('#asg_field').val(field);
				
				if($('.asg_lista .med_item').length == 0){
					var url = "{{ route('media.modal_list_media_asigna') }}" + "?tipo=" + tipo;
					asg_load_ajax_multimedia(url);
				}
            });

			// Selecciona item media
			$(document).on('click', '.asg_lista .med_item', function() {
				$(".asg_lista .med_item .med_box").removeClass('selected');
				med_item = $(this);
				med_item.find(".med_box").addClass('selected');
				med_img = med_item.find('img').attr('src');
				med_filename = med_item.data('filename');
				med_id = med_item.data('id');
				$("#asg_img").val(med_img);
				$("#asg_filename").val(med_filename);
				$("#asg_id").val(med_id);
			});

			// Paginacion MediaModal
			$(document).on('click', '#asg_med_pag .pglink', function(event) {
				event.preventDefault();
				$("#asg_med_list .row").addClass('loading').html("");
				url = $(this).attr('href');
				asg_load_ajax_multimedia(url);
			});

			// Registrar SelectMediaModal
			$('#asg_save').click(function() {
				$(".asg_msg_valida").fadeOut().text("");
				var modal = $('#AsignMediaModal')
				var field = modal.find('#asg_field').val();
				var img = modal.find('#asg_img').val();
				var filename = modal.find('#asg_filename').val();
				var id = modal.find('#asg_id').val();
				
				if(img.length == 0 || filename.length == 0){
					$(".asg_msg_valida").fadeIn().text("Ingresa los datos solicitados");
					return false;
				}else{
					// close and reset values
					$('#AsignMediaModal').modal('hide');
					$('.modal-backdrop').hide();
					$('#'+field).val(filename);
					$('.'+field).find('img').attr('src',img);
					
					// if Dropify
						// console.log(id)
						// console.log(filename)

					if ( $('input[type=file][name=' + field + ']').length )
					{
						// console.log('AAAA')
						var filedropper = $('input[type=file][name=' + field + ']').dropify();
						filedropper = filedropper.data('dropify');
						filedropper.resetPreview();
						filedropper.clearElement();
						filedropper.settings['defaultFile'] = filename;
						// filedropper.settings['defaultFile'] = space_url + filename;
						filedropper.destroy();
						filedropper.init();

						 $('input[name=multimedia_'  + field + ']').val(id)
					}
				}

			});

			// Buscaar
			$('#asg_btn_busca').click(function() {
				var q = $("#asg_q").val();
				console.log(q);
				$("#asg_med_list .row").addClass('loading').html("");
				var tipo_lista = $("#asg_med_list").data('tipo');

				var url = "{{ route('media.modal_list_media_asigna') }}" + "?q=" + q + "&tipo=" + tipo;
				console.log(url);
				asg_load_ajax_multimedia(url);
			});

			// FUNCIONES

            // carga ajax con multimedia
			function asg_load_ajax_multimedia(url){
				$.ajax({
					url: url,
					type: "GET",
					dataType : "json",
				})
				.done(function( result ) {
					if(result){
						console.log(result);
						var medias = result.data;
						var space_url = "{{ Storage::url('') }}";
						var base_url = "{{ asset('') }}";
						$("#asg_med_list .row").removeClass('loading');
						//extensiones y previews
						valid_ext1 = ["jpeg","jpg","png","gif","svg","webp"];
						valid_ext2 = ["mp4","webm","mov"];
						valid_ext3 = ["mp3"];
						valid_ext4 = ["pdf"];
						valid_ext5 = ["zip","scorm"];
						// 
						preview = base_url + 'img/icon-file.svg';
						tipo = 'archivo'; color = '#455A64';
						
						medias.forEach(media => {
							ext = (media.ext).toLowerCase();
							if(valid_ext1.indexOf(ext) !== -1){
								preview = space_url + media.file;
								tipo = 'imagen'; color = '#f6685e';
							}else if(valid_ext2.indexOf(ext) !== -1){
								preview = base_url + 'img/icon-video.svg';
								tipo = 'video'; color = '#2196f3';
							}else if(valid_ext3.indexOf(ext) !== -1){
								preview = base_url + 'img/icon-audio.svg';
								tipo = 'audio'; color = '#af52bf';
							}else if(valid_ext4.indexOf(ext) !== -1){
								preview = base_url + 'img/icon-pdf.svg';
								tipo = 'pdf'; color = '#8bc34a';
							}else if(valid_ext5.indexOf(ext) !== -1){
								preview = base_url + 'img/icon-zip.svg';
								tipo = 'scorm'; color = '#ffac33';
							}
							
							item = '<div class="col-sm-6 col-md-3 col-lg-2 mt-2 med_item" data-id="'+media.id+'" data-filename="'+media.file+'">'+
										'<div class="med_box" >'+
											'<div class="img_box">'+
												'<span class="tag" style="background:'+color+'">'+tipo +'</span>'+
												'<img src="'+preview+'"  class="img-fluid" >'+
											'</div>'+
											'<span>'+media.file+'</span>'+
										'</div>'+
									'</div>';

							$("#asg_med_list .row").append(item);
						});

						pages = genera_paginacion(result);
						$("#asg_med_pag").html(pages);
					}
				});
			}

        });    
    </script>