
const box_valoracion = document.getElementById('box_valoracion');
const box_val_init = document.getElementById('box_val_init');
const box_val_stars = document.getElementById('box_val_stars');
const box_val_icons = document.getElementById('box_val_icons');
const box_val_comment = document.getElementById('box_val_comment');
const box_val_final =  document.getElementById('box_val_final');
const btn_send_val =  document.getElementById('btn_send_val');

const box_val_stars_question =  document.getElementById('box_val_stars_question');
const box_val_icons_question =  document.getElementById('box_val_icons_question');
const box_val_comment_question =  document.getElementById('box_val_comment_question');
const box_val_comment_hint =  document.getElementById('box_val_comment_hint');

var local_data_nps = "";
var stars_selected = null;
var chars_selected = null;
var comment_added = null;


jQuery(function ($) {
    if(typeof(Storage) !=='undefined'){

        let mostrar_nps = localStorage.getItem('mostrar_nps');
        let nps_sent = localStorage.getItem('nps_sent');
        let current = new Date();
        let new_sent = (nps_sent != null) ? (parseInt(nps_sent)+86400000) < current.getTime() : false;

        if(mostrar_nps != null && mostrar_nps && (nps_sent == null || new_sent)){
            setTimeout(() => {
                box_valoracion.classList.remove('hide')
            }, 2500);
        }
    }
});


function close_box_val() {
    sendSaveComment();
    setTimeout(() => {
        box_valoracion.classList.add('hide');
    }, 300);
}

function showStars(){
    box_val_stars.classList.remove('hide');
    let data_nps = localStorage.getItem('data_nps');
    local_data_nps = JSON.parse(data_nps);
    local_data_nps.encuesta.secciones.forEach(element => {
        if(element.seccion == 1)
            box_val_stars_question.innerHTML = element.pregunta
    });
    sendSaveComment();
}

function showIcons( star ){
    if(star != undefined && star != '')
    {
        stars_selected = star;
        for (let i = 1; i < 11; i++) {
            const star_i = document.getElementById('i_star_'+i);
            star_i.classList.remove('mdi-star');
            star_i.classList.add('mdi-star-outline');
        }
        for (let i = 1; i < star+1; i++) {
            const star_i = document.getElementById('i_star_'+i);
            star_i.classList.remove('mdi-star-outline');
            star_i.classList.add('mdi-star');
        }

        local_data_nps.encuesta.secciones.forEach(element => {
            if(element.seccion == 2){
                let icons = [];
                if(star < 7){
                    icons = element.detractor.caracteristicas;
                    box_val_icons_question.innerHTML = element.detractor.pregunta;
                }else if(star > 6 && star < 9){
                    icons = element.neutral.caracteristicas;
                    box_val_icons_question.innerHTML = element.neutral.pregunta;
                }else if(star > 8){
                    icons = element.promotor.caracteristicas;
                    box_val_icons_question.innerHTML = element.promotor.pregunta;
                }
                icons.forEach((value, index) => {
                    let i = index + 1;
                    let item_text =  document.getElementById('box_val_icons_item_text' + i);
                    let item_icon =  document.getElementById('box_val_icons_item_icon' + i);
                    let item_btn =  document.getElementById('box_val_icons_item_btn' + i);

                    item_text.innerHTML = value.nombre
                    item_icon.src = value.icono
                    item_btn.dataset.char = value.id
                });
            }
        });

    }
    box_val_icons.classList.remove('hide');
    sendSaveComment();
}

function showComment(element, value){
    const icons = document.getElementsByClassName('i-icon');
    if(icons.length > 0){
        icons.forEach(el => {
            el.classList.remove('selected');
        });
    }
    element.classList.add('selected');

    chars_selected = [element.dataset.char];

    local_data_nps.encuesta.secciones.forEach(element => {
        if(element.seccion == 3){
            box_val_comment_question.innerHTML = element.pregunta
            box_val_comment_hint.placeholder = element.hint
        }
    });
    box_val_comment.classList.remove('hide');
    sendSaveComment();
}

function sendComment(){

    if(typeof(Storage) !=='undefined'){
        localStorage.removeItem('mostrar_nps')
    }

    const txt_btn_send = document.getElementById('txt_btn_send');
    const spinner_btn_send = document.getElementById("spinner_btn_send");

    txt_btn_send.innerText = "";
    btn_send_val.disabled = true;
    spinner_btn_send.classList.remove('hide');

    comment_added = box_val_comment_hint.value;

    sendSaveComment();

    setTimeout(() => {
        spinner_btn_send.classList.add('hide');
        txt_btn_send.innerText = "¡Enviado!";

        setTimeout(() => {
            box_val_init.classList.add('remove');
            box_val_stars.classList.add('remove');
            box_val_icons.classList.add('remove');
            box_val_comment.classList.add('remove');
            box_valoracion.classList.add('hide');
            setTimeout(() => {
                box_val_final.classList.remove('hide');
                setTimeout(() => {
                        box_val_final.classList.add('hide');
                }, 3000);
            }, 600);
        }, 700);
    }, 1200);
}

function sendSaveComment(){

    if(local_data_nps == "") {
        let data_nps = localStorage.getItem('data_nps');
        local_data_nps = JSON.parse(data_nps);
    }
    let id_encuesta = local_data_nps.encuesta.id;
    let nombre = local_data_nps.encuesta.nombre;
    let version = local_data_nps.encuesta.version;

    let data_nps = {
        "encuesta": {
          "id": id_encuesta,
          "nombre": nombre,
          "version": version,
          "valoracion": stars_selected,
          "caracteristicas": chars_selected,
          "comentario": comment_added
        },
        "usuario": {
          "nombre": USER_NAME,
          "dni": USER_DOCUMENT,
          "email": USER_EMAIL,
          "workspace": USER_WORKSPACE,
          "subworkspace": "",
          "workspace_slug": USER_WORKSPACE_SLUG,
          "platform": "gestor"
        }
      };

    $.ajax({
        type: "post",
        url: URL_SAVE_NPS,
        data: data_nps,
        dataType: "application/json; charset=utf-8"
    }).always(function(){
        let date = new Date();

        localStorage.setItem('nps_sent',date.getTime());
        localStorage.removeItem('data_nps');
    });
}
