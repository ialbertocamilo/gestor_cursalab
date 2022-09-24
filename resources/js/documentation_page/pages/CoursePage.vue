<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Cursos
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Esta API retorna el listado de todos los cursos creados en la plataforma.
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
                route:'/integrations/courses?page=1',
                parameters_type:[
                    {
                        title: "Parámetros (URL)",
                        parameters: [
                            {
                                name: "page",
                                type: "Número (int)",
                                description:
                                    "Número de página a consultar (100 por página)"
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
    url: base_url+'/integrations/courses?page=1',
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
        "courses": [
            {
                "year_course": "Año de creación del curso",
                "budget": "Presupuesto asignado al curso",
                "course": "Nombre del curso",
                "course_code": "Identificador del curso",
                "effort": "Esfuerzo minimo esperado en horas",
                "modality": "Modalidad del curso (regular, extracurricular o libre)",
                "schools": "Listado de escuelas a las que el curso pertenece"[
                    {
                        "school_code": "Identificador de la Escuela",
                        "school": "Nombre de la escuela"
                    }
                ],
                "total_user_completed": "cantidad de personas con el curso finalizado",
                "total_user_assignment": "cantida de personas inscritas",
            },
        ],
        "current_page": "Página actual de la consulta",
        "last_page": "Última página",
        "per_page": "Items por página (100)",
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