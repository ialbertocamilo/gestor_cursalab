<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Progreso del Curso
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Esta API retorna el progreso de los usuarios que tienen asignado este curso.
            </p>
            <descriptionApi :options="api_description_options" />
        </v-card-text>
    </v-card>
</template>
<script>
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
export default {
    components: {descriptionApi},
    data() {
        return{
            api_description_options:{
                title:'Listar los workspaces',
                type:'GET',
                route:'/integrations/course-progress/{course_code}?page=1',
                parameters_type:[
                    {
                        title: "Parámetros (URL)",
                        parameters: [
                            {
                                name: "course_code",
                                type: "Número (int)",
                                description:
                                    "Identificador del curso"
                            },
                            {
                                name: "page",
                                type: "Número (int)",
                                description:
                                    "Número de página a consultar (500 por página)"
                            }
                        ]
                    },
                    {
                        title:'Parámetros (header)',
                        parameters:[
                            {
                                name:'secretKey',
                                type:'String',
                                description:'Clave secreta asociada a la cuenta del administrador.'
                            },
                            {
                                name:'Authorization',
                                type:'String',
                                description:`
                                    Token asociado a la cuenta del administrador concatenado con el tipo de token.<br>Ejemplo:<br>
<pre class='language-js line-numbers'>
    <code>
        token:'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
    </code>
</pre>`
                            }
                        ]
                    }
                ],
                example_code:{
                    title:'Ejemplo',
                    tabs:[
                        'Request', 'Response (200)'
                    ],
                    content_tabs:[
                        {
type:'language-js',
code:
`
const base_url = '${base_url}';
var axios = require('axios');
var config = {
    method: 'get',
    url: base_url+'/integrations/course-progress/{course_code}?page=1',
    headers: { 
        'secretKey': 'f*hdj[!GbdZQ4{#zKlot', 
        'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
    }
};

axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
})
.catch(function (error) {
    console.log(error);
});`
},
{
type:'language-js',
code:
`
{
    "data": {
        "segmented_users": [
            {
                "course_code": "Código del curso",
                "course_name": "Nombre del curso",
                "document": "Identificador del usuario",
                "status_course": "Estado del curso para el usuario (Pendiente, Aprobado, Desaprobado, Encuesta Pendiente)",
                "date_start": "Fecha de inscripción en el curso"
            },
        ],
        "current_page": "Página actual de la consulta",
        "last_page": "Última página",
        "per_page": "Items por página (500)",
        "prev_page_url": "URL de la página anterior",
        "nex_page_url": "URL de la página siguiente",
        "total": "Total de items"
    }
},`
}
                ],
                }
            },
        }
    }
}
</script>