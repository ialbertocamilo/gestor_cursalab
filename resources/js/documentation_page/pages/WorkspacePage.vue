<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Workspaces
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Los Workspaces son espacios donde se organiza toda la información.
                <br>Este recurso sirve principalmente para conocer los módulos (grupos) donde se organizan los usuarios.
                <br>{{ is_inretail ? 'Ejemplo: Workspace: Intercorp Retail , Módulos dentro del workspace: Agora,InDigital XP,Intercorp Retail.' : '' }} 
            </p>
            <descriptionApi :options="api_description_options" :set_responses="true" />
        </v-card-text>
    </v-card>
</template>
<script>
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
const is_inretail =  base_url.includes('inretail');
export default {
    components: {descriptionApi},
    data() {
        return{
            is_inretail : is_inretail,
            api_description_options:{
                title:'Listar los workspaces',
                type:'GET',
                route:'/integrations/workspaces',
                parameters_type:[
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
    url: base_url+'/integrations/workspaces',
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
        "workspaces": [
            {
                "workspace_id": "Identificador del workspace",
                "name": "Nombre del workspace",
                "subworkspaces":"Listado de subworkspaces ('modulos') asociados"
            }
        ]
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