<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Inactivar usuarios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Este proceso permite cambiar el estado de los usuarios a "inactivo". <br>
                También aplica para la REVERSA DE ALTAS. <br>
                Cuando se realiza este proceso se actualiza el dato "termination_date" (Fecha de cese del usuario) con la fecha recibida en la colección.
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
                title:'Inactivar usuarios',
                type:'POST',
                route:'/integrations/inactivate-users',
                parameters_type:[
                    {
                        title:'Parámetros (body)',
                        parameters:[
                            {
                                name:'identificator',
                                type:'Texto (String)',
                                description:'El tipo de identificador que usará para enviar la lista de usuarios (puedes ser "document" (dni, pasaporte), "username", "email").'
                            },
                            {
                                name:'users',
                                type:'Array (String)',
                                description:'Colección de usuarios con la fecha de terminación o cese. Formato de fecha "yyyy-mm-dd"'
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
    const data = JSON.stringify({
        {
        "identificator":"document",
        "users":[
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
                ["12345678", "2022-08-30"],
            ]
        }
    );
    const config = {
        method: 'post',
        url: base_url+'/integrations/inactivate-users',
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
"data":{
    "quantity_inactivated": "Cantidad de usuarios que han sido desactivados.",
    "amount_errors": "Cantida|d de errores encontrados en la petición.",
    "processed_data": "Cantidad de datos procesados obtenidos de la petición",
    "errors":"Listado de errores"{
        "message": "Mensaje del error",
        "value": "El valor enviado en la petición"
    },
}`
}
                ],
                }
            },
        }
    }
}
</script>