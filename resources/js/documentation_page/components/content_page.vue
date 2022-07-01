<template>
    <ul >
        <li v-for="(item,index) in list" :key="index">
            {{item.title}}
            <defaultTabs v-if="item.isTab" :tabs="item.tabs" :content_tabs="item.content_tabs" /> 
            <!-- <pre v-else v-text="item.code">
            </pre> -->
        </li>
    </ul>
</template>
<script>
import defaultTabs from '../components/default_tabs.vue';
export default {
    components: {defaultTabs},
    props: {
        list:Array,
    }
}
</script>