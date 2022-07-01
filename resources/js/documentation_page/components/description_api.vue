<template>
    <div>
        <p class="subtitle-1 font-weight-bold mb-0" style="color:#212121" v-text="options.title"></p >
        <p v-show="options.subtitle" class="subtitle-2  ml-2" style="color:#212121" v-html="options.subtitle"></p>
        <p>
            <v-chip
                label
                outlined
                class="mr-2"
            >
                {{options.type}} 
            </v-chip>
            {{options.route}}
        </p>
        <div v-for="(parameter_type,index) in options.parameters_type" :key="index">
            <p class="subtitle-1 font-weight-bold mb-0 ml-2" style="color:#212121" v-text="parameter_type.title"></p>
            <div class="mb-6 ml-2" v-for="(parameter,index) in parameter_type.parameters" :key="index">
                <div>
                    <v-chip color="pink" text-color="white" class="ma-2" label small><b class="pr-2">{{parameter.name}}</b></v-chip>
                </div>
                <v-divider class="ml-2 mb-2"></v-divider>
                <div class="ml-2">
                    <p class="mb-0" v-text="`Tipo: ${parameter.type}`"></p>
                    <p class="mb-0" v-html="parameter.description"></p>
                </div>
            </div>
        </div>
        <div class="ml-2">
            <p class="subtitle-1 font-weight-bold mb-0" style="color:#212121" v-text="options.example_code.title"></p >
            <defaultTabs :tabs="options.example_code.tabs" :content_tabs="options.example_code.content_tabs" /> 
        </div>
    </div>
</template>
<script>
import defaultTabs from '../components/default_tabs.vue';
export default {
    components: {defaultTabs},
    props: {
        options:Object
    }
}
</script>
<style>
.language-js{
    background-color:#f5f2f0 !important;

}
</style>