<template>
    <div>
        <v-card elevation="0" class="mx-10">
             <v-card-title class="font-weight-bold">
                Token
            </v-card-title>
            <v-card-text class="ml-2">
                <p>Es una cadena de texto codificado que nos ayuda a controlar el acceso al uso de nuestras API's. 
                    Esta clave será entregada junto con los permisos de administrador del gestor y la fecha de duración es de 1 mes. 
                </p>
                <descriptionApi :options="api_description_options" />
            </v-card-text>
        </v-card>
    </div>
</template>
<script>
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
export default {
    components: {descriptionApi},
    data(){
        return{
            api_description_options:{
                title:'Generar un nuevo token',
                type:'POST',
                route:'/integrations/auth_user',
                parameters_type:[
                    {
                        title:'Parametros (body)',
                        parameters:[
                            {
                                name:'email',
                                type:'String',
                                description:'Es el correo asociado a la cuenta administrador del gestor.'
                            },
                            {
                                name:'password',
                                type:'String',
                                description:'Es la contraseña asociado a la cuenta administrador del gestor.'
                            },
                        ]
                    },
                    {
                        title:'Parametros (header)',
                        parameters:[
                            {
                                name:'secretKey',
                                type:'String',
                                description:'Clave secreta asociada a la cuenta del administrador.'
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
                            type:'language-javascript',
                            code:
`
const base_url = '${base_url}';
const axios = require('axios');
const data = JSON.stringify({"email":"admin@admin.com","password":"4239872439"});
const config = {
    method: 'post',
    url: base_url+'/integrations/auth_user',
    headers: { 
        'secretKey': '982alsdh$%as', 
        'Content-Type': 'application/json'
    },
    data : data
};
axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
}).catch(function (error) {
    console.log(error);
});
`
                        },
                        {
                            type:'language-json',
                            code:
`
{
    "data": {
        "access_token": "Token para hacer la consulta.",
        "token_type": "Tipo de token.(Bearer)",
        "expires_in": "Tiempo de expiración.",
        "expires_in_format": "Formato del tiempo de expiración (minutes,hours,days)"
    }
}
`
                        }
                    ]
                }
            },
        }
    }
}
</script>