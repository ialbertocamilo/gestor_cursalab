<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Criterios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Los criterios son atributos que tienen los usuarios de la plataforma, los cuales se pueden usar para segmentar los contenidos. 
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
                title:'Listar los criterios',
                type:'GET',
                route:'/integrations/criterions',
                parameters_type:[
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
var axios = require('axios');
var config = {
    method: 'get',
    url: base_url+'/integrations/criterions',
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
"data": {
    "criterions": [
        {
            "criterion_id":"Identificador del criterio",
            "criterion_code": "Nombre del criterio",
            "data_type": "Tipo del criterio (texto,fecha,número)",
        },
    ]
}`
}
                ],
                }
            },
        }
    }
}
</script>