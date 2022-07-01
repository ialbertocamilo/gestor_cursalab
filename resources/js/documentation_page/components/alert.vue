<template>
    <div :style="`background-color:${background};border:1px solid ${border};color:${text_color}`" class="rounded-lg px-4 py-2">
        <v-icon v-text="icon"></v-icon>
        <span v-text="text"></span>
    </div>
</template>
<script>
export default {
    props: {
        icon: String,
        background:String,
        border:String,
        text:String,
        text_color:{
            default:'black',
        }
    }
}
</script>