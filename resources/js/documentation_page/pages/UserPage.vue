<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
           Usuarios
        </v-card-title>
        <v-card-text class="ml-2">
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
                title:'Actualizar/Crear usuarios',
                subtitle:`El API toma como identificador el atributo de acción, este puede tomar el valor de Nuevo (crear un usuario) y datos (actualizar usuario).`,
                type:'POST',
                route:'/integrations/update_create_users',
                parameters_type:[
                    {
                        title:'Parametros (body)',
                        parameters:[
                            {
                                name:'usuarios',
                                type:'Array de usuarios(objeto)',
                                description:`
                                    Listado de usuarios a actualizar/crear<br>
                                    <ul>
                                        atributos:"Módulo, DNI, Nombres y apellidos, Botica, Grupo, Cargo, Género, Carrera, Ciclo, Acción"<br>
                                        Ejemplo:<br>
<pre class='language-js line-numbers'><code>
    "users": [
        {
            "modulo":"Capacitación Mifarma",
            "dni":""75627382"",
            "nombre_apellidos":"Rodrigo Perez Ramirez",
            "genero":"F",
            "botica":"Paita",
            "grupo":"Grupo 18",
            "cargo":"Tecnico de Farmacia",
            "carrera":"Tecnico de Farmacia",
            "ciclo":"Ciclo 1",
            "accion":"Nuevo"
        }
    ]
</code></pre>
                                    </ul>                                `
                            }
                        ]
                    },
                    {
                        title:'Parametros (header)',
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
let axios = require('axios');
let data = JSON.stringify({
    "users":
        [
            {
                "modulo":"Capacitación Mifarma",
                "dni":""75627382"",
                "nombre_apellidos":"Rodrigo Perez Ramirez",
                "genero":"F",
                "botica":"Paita",
                "grupo":"Grupo 18",
                "cargo":"Tecnico de Farmacia",
                "carrera":"Tecnico de Farmacia",
                "ciclo":"Ciclo 1",
                "accion":"Nuevo"
            }
        ]
    }
);
var config = {
    method: 'post',
    url: base_url+'/integrations/update_create_users',
    headers: { 
        'secretKey': 'f*hdj[!GbdZQ4{#zKlot', 
        'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ...', 
        'Content-Type': 'application/json'
    },
    data : data
};

axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
}).catch(function (error) {
    console.log(error);
});`}
,{
type:'language-js',
code:
`
{
    "data": {
        "inserted_users": "Cantidad de usuarios insertados.",
        "updated_users": "Cantidad de usuarios actualizados.",
        "amount_errors": "Cantidad de errores encontrados.",
        "processed_data": "Cantidad de data recibida.",
        "errors": "Listado de errores encontrados en la api." /[
            {
                "error": "Listado de errores encontrados en los atributos del usuario",
                "err_data_original": "Data original del usuario enviada por el api"
            }
        ]
    }
}`
}],
                }
            },
        }
    }
}
</script>