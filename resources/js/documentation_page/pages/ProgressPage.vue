<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Avance de usuarios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Este proceso retorna el avance de los usuarios en la plataforma,
                dentro de un rango de fechas.<br />
                El avance corresponde a todos los cursos COMPLETADOS por los
                usuarios.
            </p>
            <!-- , dentro del rango de fechas establecido. -->
            <descriptionApi :options="api_description_options" />
        </v-card-text>
    </v-card>
</template>
<script>
import descriptionApi from "../components/description_api.vue";
let base_url = window.location.origin;
export default {
    components: { descriptionApi },
    data() {
        return {
            api_description_options: {
                title: "User progress",
                type: "GET",
                route: "/integrations/user-progress?page=1",
                parameters_type: [
                    {
                        title: "Parámetros (URL)",
                        parameters: [
                            // {
                            //     name: "start_date",
                            //     type: "Fecha (date) - Formato (YYYY-MM-DD)",
                            //     description:
                            //         "Se indica la fecha inicial a consultar."
                            // },
                            // {
                            //     name: "end_date",
                            //     type: "Fecha (date) - Formato (YYYY-MM-DD)",
                            //     description:
                            //         "Se indica la fecha final a consultar."
                            // },
                            {
                                name: "page",
                                type: "Número (int)",
                                description:
                                    "Número de página a consultar (500 por página)"
                            }
                        ]
                    },
                    {
                        title: "Parámetros (header)",
                        parameters: [
                            {
                                name: "secretKey",
                                type: "String",
                                description:
                                    "Clave secreta asociada a la cuenta del administrador."
                            },
                            {
                                name: "Authorization",
                                type: "String",
                                description: `
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
                example_code: {
                    title: "Ejemplo",
                    tabs: ["Request", "Response (200)"],
                    content_tabs: [
                        {
                            type: "language-js",
                            code: `   
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
            url: base_url+'/integrations/user-progress',
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
                            type: "language-js",
                            code: `
    "data":{

            "users":[
                {
                    "workspace" : "Espacio de trabajo (Farmacias Peruanas, Real Plaza, Química Suiza, etc)",
                    "module" : "Módulo (empresa) al que pertecene el usuario (Mifarma, Inkafarma, etc.)",
                    "user_name" : "Valor único que identifica al usuario",
                    "name" : "Nombre completo del usuario",
                    "total_assigned" : "Cantidad total de cursos asignados",
                    "total_completed" : "Cantidad total de cursos completados",
                    "total_percentage" : "Porcentaje total de avance en la plataforma",
                    "completed_courses":[
                        {
                            "school_code" : "Identificador de la Escuela",
                            "school" : "Escuela a la que pertenece el curso",
                            "course_code" : "Identificador del curso",
                            "course" : "Nombre del Curso",
                            "modality" : "Modalidad del curso (regular, extracurricular o libre)",
                            "score" : "Nota promedio que el usuario tiene en el curso",
                            "percentage" : "Porcentaje de avance del usuario en el curso",
                            "result" : "El resultado del usuario en el curso (aprobado, desaprobado)",
                            "date" : "Fecha de curso completado",
                        }
                    ]
                }
            ],
            "current_page": "Página actual de la consulta",
            "last_page": "Última página",
            "per_page": "Items por página (500)",
            "prev_page_url": "URL de la página anterior",
            "nex_page_url": "URL de la página siguiente",
            "total": "Total de items"
        }
    `
                        }
                    ]
                }
            }
        };
    }
};
</script>