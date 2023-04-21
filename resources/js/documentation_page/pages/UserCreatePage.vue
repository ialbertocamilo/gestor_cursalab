<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
           Alta de usuarios (crear)
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Este proceso permite únicamente crear usuarios mediante el envió de sus datos personales y sus criterios correspondientes. <br>
                La validación de creación de usuario se hace mediante el valor del documento, en caso el documento ya se encuentre registrado no se creará ni actualizará los datos la api lo retornará dentro del listado de errores.
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
                title:'Crear usuarios',
                type:'POST',
                route:'/integrations/create-users',
                parameters_type:[
                    {
                        title:'Parámetros (body)',
                        parameters:[
                            {
                                name:'usuarios',
                                type:'Array de usuarios(objeto)',
                                description:`
                                    Listado de usuarios a crear. Cada usuario contiene atributos estáticos y dinámicos<br>
                                    <ul>
                                        Estáticos:"active","document", "fullname","phone_number", etc.<br>
                                        Dinámicos: "criterions"<br>
                                        Ejemplo:<br>
<pre class='language-js line-numbers'><code>
    {
        "workspace_id":"Identificador del workspace",
        "users": [
            {
                "active": boolean,
                "document": text,
                "person_number": text,
                "fullname": text,
                "name": text,
                "lastname": text,
                "surname": text,
                "username": text (Optional),
                "phone_number": text,
                "email": text,
                "criterions": {
                    "module":text,
                    "user_action_id":text,
                    "document_type_id":text,
                    "business_unit_id":text,
                    "business_unit_name":text,
                    "gender":text,
                    "position_name":text,
                    "position_code":text,
                    "date_start":text,
                    "termination_date":date,
                    "seniority_date":date,
                    "birthday_date":date,
                    "phone_type_id":text,
                    "aplica_a_bono":text,
                    "tipo_de_bono":text,
                    "grupo_ocupacional":text,
                    "location_code":text,
                    "location_name":text,
                    "department_name":text,
                    "department_code":text,
                    "modalidad_de_trabajo":text,
                    "department_name_nivel_1":text,
                    "department_name_nivel_2":text,
                    "department_name_nivel_3":text,
                    "department_name_nivel_4":text,
                    "department_name_nivel_5":text,
                    "department_name_nivel_6":text,
                    "email_type":text,
                    "national_identifier_number_manager":text,
                    "nombre_de_jefe":text,
                    "posicion_jefe":text,
                    "clasificacion_de_evd":text,
                    "gor_gerente_de_área":text,
                    "botica":text,
                    "grupo":text,
                    "zonal":text,
                    "correo_zonal":text,
                    "tipo_de_publico":text,
                    "division":text,
                    "area":text,
                    "region":text,
                    "region_de_tienda":text,
                    "correo_jefe":text,
                    "grupos_de_supervision_supply":text,
                    "gerente_de_area_o_mall":text,
                }
                
            }
        ]
    }
</code></pre>
                                    </ul>                                `
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
let axios = require('axios');
let data = JSON.stringify({
    "users":
        [
                    {
            "active": boolean,
            "document": text,
            "person_number": text,
            "fullname": text,
            "name": text,
            "lastname": text,
            "surname": text,
            "username": text,
            "phone_number": number,
            "email": text,
            "criterions": {
                "module":text,
                "user_action_id":text,
                "document_type_id":text,
                "business_unit_id":text,
                "business_unit_name":text,
                "gender":text,
                "position_name":text,
                "position_code":text,
                "date_start":text,
                "termination_date":date,
                "seniority_date":date,
                "birthday_date":date,
                "phone_type_id":text,
                "aplica_a_bono":text,
                "tipo_de_bono":text,
                "grupo_ocupacional":text,
                "location_code":text,
                "location_name":text,
                "department_name":text,
                "department_code":text,
                "modalidad_de_trabajo":text,
                "department_name_nivel_1":text,
                "department_name_nivel_2":text,
                "department_name_nivel_3":text,
                "department_name_nivel_4":text,
                "department_name_nivel_5":text,
                "department_name_nivel_6":text,
                "email_type":text,
                "national_identifier_number_manager":text,
                "nombre_de_jefe":text,
                "posicion_jefe":text,
                "clasificacion_de_evd":text,
                "gor_gerente_de_área":text,
                "botica":text,
                "grupo":text,
                "zonal":text,
                "correo_zonal":text,
                "tipo_de_publico":text,
                "division":text,
                "area":text,
                "region":text,
                "region_de_tienda":text,
                "correo_jefe":text,
                "grupos_de_supervision_supply":text,
                "gerente_de_area_o_mall":text,
            }
            
        }
        ]
    }
);
var config = {
    method: 'post',
    url: base_url+'/integrations/update-create-users',
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
        "amount_inserted_users": "Cantidad de usuarios insertados.",
        "amount_errors":"Cantidad de errores encontrados."
        "processed_data": "Cantidad de data recibida.",
        "inserted_users": "Documento y identificador del workspace de los usuarios insertados."
        "errors": "Listado de errores encontrados en la api." 
    }
}`
}],
                }
            },
        }
    }
}
</script>