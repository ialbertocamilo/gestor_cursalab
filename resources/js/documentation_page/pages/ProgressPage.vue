<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Progreso de usuarios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Esta API retorna el avance de los usuarios dentro de un rango de fechas.<br>
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
                    title:'Activar usuarios',
                    type:'POST',
                    route:'/integrations/progress',
                    parameters_type:[
                        {
                            title:'Parámetros (body)',
                            parameters:[
                                {
                                    name:'start_date',
                                    type:'Texto (String) - Formato (YYYY-MM-DD)',
                                    description:'Se indica la fecha inicial a consultar.'
                                },
                                {
                                    name:'end_date',
                                    type:'Texto (String) - Formato (YYYY-MM-DD)',
                                    description:'Se indica la fecha final a consultar.'
                                },
                                {
                                    name:'page',
                                    type:'Número (int)',
                                    description:'Número de página a consultar.(100 por página)'
                                }
                            ],
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
        const axios = require('axios');
        const data = JSON.stringify(
            {
            "start_date":"2022/21/04/",
            "end_date":"21/05/2022",
            "page":"1",
            }
        );
        const config = {
            method: 'post',
            url: base_url+'/integrations/progress',
            headers: { 
                'secretKey': 'asd-i4qEJK46[hdj', 
                'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9....', 
                'Content-Type': 'application/json'
            },
            data : data
        };
        axios(config).then(function (response) {
            console.log(JSON.stringify(response.data));
        })
        .catch(function (error) {
            console.log(error);
        });
    `
    },
    {
    type:'language-js',
    code:
    `
    "data":[{
        "Workspace":"Espacio de trabajo (Farmacias Peruanas, Real Plaza, Química Suiza, etc)",
        "Módulo":"Módulo al que pertecene el usuario.",
        "Identificador":"Valor único que identifica al usuario",
        "Apellidos y Nombres":"Nombre completo del usuario",
        "Genero":"Género del usuario",
        "Estado (Usuario)":"Los valores a tomar son Activo o Inactivo",
        "code_escuela":"Identificador de la escuela",
        "Escuela":"Escuela a la que pertenece el curso",
        "Modalidad":"Modalidad del curso (regular,extracurricular o libre)",
        "code_curso":"Identificador del curso",
        "Curso":"Nombre del Curso",
        "Nota promedio":"Nota promedio que el usuario tiene en el curso",
        "Temas asignados":"Cantidad de temas asignados al usuario",
        "Temas completados":"Cantidad de temas completados por el usuario",
        "Porcentaje":"Porcentaje de avance del usuario en el curso",
        "Resultado":"El resultado del usuario en el curso(pendiente,encuesta pendiente,aprobado,desaprobado)",
    }]`
    }
                    ],
                    }
                },
            }
        }
    }
    </script>