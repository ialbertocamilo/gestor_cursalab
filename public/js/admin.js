$(function(){

	if ($('div').hasClass('camposmagicos')) {

		campos_magicos();
		check_add_or_edit();

		function check_add_or_edit(){
			// tabla opciones
			var el_rptas_json = $("input[name='rptas_json']");
			if (el_rptas_json.length) {
				if ( el_rptas_json.val() != "" ) {
					var rptas_json_string = el_rptas_json.val();
					var rpta_ok = $("input[name='rpta_ok']").val();
					console.log('editar');
					modo_edit(rptas_json_string, rpta_ok);
				}
				// selectbox
				rpta_ok = $("input[name='rpta_ok']");
				if ( rpta_ok.val() != "" ) {
					$("#mrpta_ok").val( rpta_ok.val() );
				}
			}

		}

		function campos_magicos(){
			var preg = $("input[name='pregunta']");
			preg_div = (preg.parent().parent());
			
		  	el_add_input = '<div class="form-group row">'+
		                    '<div class="col-sm-3 form-control-label">'+
		                        '<label>Agregar Opción</label>'+
		                    '</div>'+
		                    '<div class="col-sm-4">'+
	                        	'<input class="form-control" type="text" name="rpta" id="mrpta" value="">'+
							'</div>'+
		                    '<div class="col-sm-3">'+
		                        '<button class="btn btn-success" id="addrpta"><i class="fa fa-plus" style="font-size:16px;"></i> Agregar</button>'+
		                    '</div>'+
		                  '</div>';

		    el_add_tabla =  '<table class="table" id="mtable" style="display: none;background-color: #ebeaf7;margin:25px 0;">'+
		                        '<thead><tr><th>Item</th><th>Opción</th><th>&nbsp;</th></tr></thead>'+
		                        '<tbody id="btable">'+
		                      '</table>';

		    el_add_rptaok =  '<div class="form-group row">'+
		    					'<div class="col-sm-3 form-control-label">'+
		                        	'<label>Opción Correcta</label>'+
			                    '</div>'+
			                    '<div class="col-sm-9">'+
		                        	'<select name="rpta_ok" id="mrpta_ok" class="form-control">'+
			                        '</select>'+
								'</div>'+
		                      '</div>';

		    el_to_add = el_add_input + el_add_tabla + el_add_rptaok;
			preg_div.after(el_to_add);
		}

		function modo_edit(rptas_json_string, rpta_ok){

			rptas_json = JSON.parse(rptas_json_string);
			console.log(rptas_json);
			for (var key in rptas_json) {
				if (rptas_json.hasOwnProperty(key)) {
				  	console.log(rptas_json[key]);
					add_opciones(key, rptas_json[key]);
				}
			}
		}

		//preguntas
	    $("#addrpta").on('click', function(event){
			event.preventDefault();
			rptadom = $('#mrpta');
			rpta = rptadom.val().trim();
			rpta = rpta.replace(/['"“”;:]/g, '');
			console.log(rpta);
			if (rpta.length > 0) {
				rptadom.val('').focus();
				if ($("#mrpta_ok option").length > 0) {
				  conta = parseInt($("#mrpta_ok option:last-child").val()) + 1;
				}else{
				  conta = 1;
				}
				//
				add_opciones(conta, rpta);
				//
				genera_json();
				set_opt_ok();
			}
	    });

	   	// pasa valor de rpta ok
	    $("#mrpta_ok").on('change', function(){
		    set_opt_ok();
	    });

	    // Opciones Remover
	    $("#btable").on('click','.xdelitem', function(event){
	      event.preventDefault();
	      iditem = $(this).data('id');
	      $("#mrpta_ok option[value='"+iditem+"']").remove();
	      $("#pfila"+iditem).remove();
	      if ($("#mrpta_ok option").length == 0) { $("#mtable").hide(); }
	      set_opt_ok();
	    });

	    function add_opciones(conta, rpta){
			cad = '<tr id="pfila'+conta+'"><td class="pid">'+conta+'</td><td class="prpta">'+rpta+'</td><td><i class="fas fa-trash-alt xdelitem" role="button" data-id="'+conta+'"></i></td></tr>';
			$('#btable').append(cad);
			$('#mrpta_ok').append('<option value="'+conta+'">'+rpta+'</option>');
			$("#mtable").show(); 
	    }

	    function genera_json(){
			var cadena = "{";
			$('#btable tr').each(function () {
				var id = $(this).find('.pid').text();
				var rpta = $(this).find('.prpta').text();
				cadena += '"'+ id+'":"'+rpta+'",';
			});
			cadena = cadena.substring(0, cadena.length - 1);
			cadena += "}";
			var rpta_json = $("input[name='rptas_json']");
			rpta_json.val(cadena);
	    }

	    function set_opt_ok(){
	    	var rpta_ok = $("input[name='rpta_ok']");
			rpta_ok.val($('#mrpta_ok').val());
	    }
	}


	/* Preguntas para Encuestas */
	if ($('div').hasClass('camposmagicos_encuestas')) {

		// Cargar campos magicos, pero ocultos
		campos_magicos();
		check_add_or_edit();

		if ($("#tipopreg option:selected" ).val() != 'texto' && $("#tipopreg option:selected" ).val() != 'califica') {
			$("#addopt, #mtable").fadeIn();
		}
		
		// Mostrar campos segun "TIPO DE PREGUNTA"
	    $('#tipopreg').on('change', function() {

	      var tipopreg = $("#tipopreg option:selected" ).val();

			if (tipopreg == 'texto') {
			    $("#addopt, #mtable").fadeOut();
			    $("#opciones").val("{}");
			}
			else if(tipopreg == 'califica'){
				$("#addopt, #mtable").fadeOut();
			    $("#opciones").val('{"1":"Califica"}');	
			}
			else{
				$("#addopt, #mtable").fadeIn();
			}
	    });

		// Funciones para SIMPLE, MULTIPLE, CALIFICAR
		
		function check_add_or_edit(){
			// tabla opciones
			var el_rptas_json = $("input[name='opciones']");
			if (el_rptas_json.length) {
				if ( el_rptas_json.val() != "" ) {
					var rptas_json_string = el_rptas_json.val();
					modo_edit(rptas_json_string);
				}else{
					$("#opciones").val("{}");
				}
			}
		}

		function campos_magicos(){
			var preg = $("input[name='titulo']");
			preg_div = (preg.parent().parent());
			
		  	el_add_input = '<div class="form-group row" id="addopt" style="display: none;">'+
		                    '<div class="col-sm-3 form-control-label">'+
		                        '<label>Agregar Opción</label>'+
		                    '</div>'+
		                    '<div class="col-sm-4">'+
	                        	'<input class="form-control" type="text" name="rpta" id="mrpta" value="">'+
							'</div>'+
		                    '<div class="col-sm-3">'+
		                        '<button class="btn btn-success" id="addrpta"><i class="fa fa-plus" style="font-size:16px;"></i> Agregar</button>'+
		                    '</div>'+
		                  '</div>';

		    el_add_tabla =  '<table class="table" id="mtable" style="display: none; background-color: #ebeaf7;margin:25px 0;">'+
		                        '<thead><tr><th>Item</th><th>Opción</th><th>&nbsp;</th></tr></thead>'+
		                        '<tbody id="btable">'+
		                      '</table>';

		    el_to_add = el_add_input + el_add_tabla;
			preg_div.after(el_to_add);
		}

		function modo_edit(rptas_json_string){

			rptas_json = JSON.parse(rptas_json_string);
			for (var key in rptas_json) {
				if (rptas_json.hasOwnProperty(key)) {
				  	// console.log(rptas_json[key]);
					add_opciones(key, rptas_json[key]);
				}
			}
		}

		//preguntas
	    $("#addrpta").on('click', function(event){
			event.preventDefault();
			rptadom = $('#mrpta');
			rpta = rptadom.val().trim();
			rpta = rpta.replace(/['"“”;:]/g, '');
			console.log(rpta);
			if (rpta.length > 0) {
				rptadom.val('').focus();
				//
				var ultima_fila = $("#btable").find("tr").last(); 
				if (ultima_fila.length > 0) {
				  conta = parseInt(ultima_fila.find('.pid').text()) + 1;
				}else{
				  conta = 1;
				}

				add_opciones(conta, rpta);
				//
				genera_json();
			}
	    });

	   	// Opciones Remover
	    $("#btable").on('click','.xdelitem', function(event){
	      event.preventDefault();
	      iditem = $(this).data('id');
	      $("#pfila"+iditem).remove();
	    });

	    function add_opciones(conta, rpta){
			cad = '<tr id="pfila'+conta+'"><td class="pid">'+conta+'</td><td class="prpta">'+rpta+'</td><td><i class="fas fa-trash-alt xdelitem" role="button" data-id="'+conta+'"></i></td></tr>';
			$('#btable').append(cad);
			
	    }

	    function genera_json(){
			var cadena = "{";
			$('#btable tr').each(function () {
				var id = $(this).find('.pid').text();
				var rpta = $(this).find('.prpta').text();
				cadena += '"'+ id+'":"'+rpta+'",';
			});
			cadena = cadena.substring(0, cadena.length - 1);
			cadena += "}";
			var rpta_json = $("input[name='opciones']");
			rpta_json.val(cadena);
	    }

	}
	
});