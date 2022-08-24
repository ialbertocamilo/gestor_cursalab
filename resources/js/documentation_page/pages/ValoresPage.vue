<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Valores de los Criterios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Cada criterio tiene un conjunto de valores que se pueden asociar a los usuarios, por ejemplo si existe un criterio "GÉNERO" este puede tener como valores
                "Masculino", "Femenino", "Otros". 
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
                title:'Listar los valores por criterio',
                type:'GET',
                route:'/integrations/criterion/{criterion_id}/values',
                parameters_type:[
                    {
                        title:'Parámetros (url)',
                        parameters:[
                            {
                                name:'criterion_id',
                                type:'Integer',
                                description:'Es el identificador obtenido en el api de listado de criterios.'
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
    url: base_url+'/integrations/criterion/2/values',
    headers: { 
        'secretKey': 'f*hdj[!GbdZQ4{#zKlot', 
        'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ...'
    }
};

axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
})
.catch(function (error) {
    console.log(error);
});`
},{
type:'language-js',
code:
`
{
    "data": {
        "criterions": {
            "criterion_code": "Nombre del criterio",
            "length": "Longitud de caracteres que acepta los valores de este criterio",
            "formats": "El tipo de formato que acepta los valores de este criterio."
            "values": "Lista de valores que tiene el criterio"
        }
    }
}`
}
                    ],
                }
            },
        }
    }
}
</script>