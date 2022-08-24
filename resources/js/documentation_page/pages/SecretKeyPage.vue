<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Clave Secreta
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
               La clave secreta es una cadena conformada por números y letras de 15 caracteres que será necesario para el uso de las API's. 
               Esta clave será entregada junto con los permisos de administrador del gestor y no tienen fecha de caducidad.
            </p>
            <alert 
                background='#F8F8FB'
                border='#C1C1FF'
                icon='mdi-information-outline'
                text="Cada cuenta de administrador puede tener una clave secreta asociada para utilizar las API's."
                class="mb-6"
            />
            <descriptionApi :options="api_description_options" />
        </v-card-text>
    </v-card>
</template>
<script>
import alert from '../components/alert.vue';
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
export default {
    components: {alert,descriptionApi},
    data(){
        return{
            api_description_options:{
                title:'Obtener Clave Secreta',
                type:'POST',
                route:'/integrations/secret_key',
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
                                description:'Es la contraseña asociada a la cuenta administrador del gestor.'
                            },
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
    url: base_url+'/integrations/secret_key',
    headers: { 
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
        "secretKey": "Clave Secreta necesaria para el uso de las API's"
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