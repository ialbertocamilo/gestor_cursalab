<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Listado de Usuarios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Esta API retorna el listado de usuarios según workspace o documento.
            </p>
            <descriptionApi :options="api_description_options" :set_responses="true" />
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
                route:'/integrations/users?page=1&workspace_id=5&document=71313694',
                parameters_type:[
                    {
                        title: "Parámetros (URL)",
                        parameters: [
                            {
                                name: "page",
                                type: "Número (int)",
                                description:
                                    "Número de página a consultar (500 por página)"
                            },
                            {
                                name: "workspace_id (Opcional)",
                                type: "Número (int)",
                                description:
                                    "Identificador del workspace"
                            },
                            {
                                name: "document (Opcional)",
                                type: "Número (int)",
                                description:
                                    "Identificador del usuario"
                            },
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
    url: base_url+'/integrations/users?page=1&workspace_id=5&document=71313694',
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
        "users": [
            {
                "active": "Estado del usuario: Activo(true) , Inactivo(false)",
                "document": "Identificador del usuario",
                "person_number": "Número de persona",
                "fullname": "Nombre completo del usuario",
                "name": "Nombres",
                "lastname": "Apellido Paterno",
                "surname": "Apellido Materno",
                "username": "Nombre de usuario",
                "phone_number": "Número de celular",
                "email": "Email del usuarios",
                "criterions": "Listado de criterios asociados al usuario" []
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