<template>
    <div>
        <DefaultToggle activeLabel="Habilitar contenido para AI" inactiveLabel="Habilitar contenido para AI"
            @onChange="changeIaConvertValue" :disabled="limits.media_ia_converted >= limits.limit_allowed_media_convert" :value="false" />
        <div class="mt-4 d-flex align-items-center">
            <v-btn class="py-1" color="primary" outlined text
                style="border-radius: 15px;border-color: #5458ea;height: auto;">
                <span>{{limits.media_ia_converted}}/{{limits.limit_allowed_media_convert }}</span>
                <img width="22px" class="ml-2" src="/img/ia_convert.svg">
            </v-btn>
            <span class="ml-2">
                Para alcanzar la cantidad máxima de opciones para la transcripción de contenido multimedia a AI
            </span>
        </div>
        <div class="mt-4 d-flex align-items-center">
            <span>
                Quieres seguir disfrutando de los beneficios que te brinda la AI
            </span>
            <v-btn color="primary" outlined text style="border-color: #5458ea;">
                <img width="22px" src="/img/premiun.svg">
                <span class="text-bolder" >Solicítalo hoy</span>
            </v-btn>
        </div>
    </div>
</template>

<script>
export default {
    props:{
        limits:{
            type:Object,
            default: function () {
                return {
                    limit_allowed_media_convert:0,
                    limit_allowed_ia_evaluations:0,
                    media_ia_converted:0
                }; 
            }
        }
    },
    data(){
        return {
            ia_convert:null,
        }
    },
    methods: {
        changeIaConvertValue(value){
           this.$emit('onChange',value)
        }
    }
}
</script>
