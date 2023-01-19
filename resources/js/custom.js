export default {
    methods: {
        queryStatus(seccion = null,accion = null){

            let nps_sent = localStorage.getItem('nps_sent');
            let current = new Date();
            let new_sent = (nps_sent != null) ? (parseInt(nps_sent)+86400000) < current.getTime() : false;

            if(nps_sent == null || new_sent){
                if(typeof(nps_data) !== 'undefined' && nps_data != null){
                    nps_data.forEach(element => {
                        if(element.seccion == seccion && element.accion_completada == accion){

                            if(typeof(Storage) !== 'undefined'){
                                localStorage.setItem('mostrar_nps',true);
                                localStorage.setItem('data_nps',JSON.stringify(element));
                            }
                            let box_valoracion = document.getElementById('box_valoracion');
                            let box_val_init_btn_text = document.getElementById('box_val_init_btn_text');

                            setTimeout(() => {

                                box_val_init_btn_text.innerHTML = element.encuesta.texto_boton
                                box_valoracion.classList.remove('hide');

                                if(typeof(Storage) !== 'undefined'){
                                    localStorage.removeItem('mostrar_nps');
                                }
                            }, 3500);
                            return;
                        }
                    });
                }
            }
        },

    }
}
