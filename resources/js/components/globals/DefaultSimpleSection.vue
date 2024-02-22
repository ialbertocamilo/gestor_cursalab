<template>
    <div :class="[ marginy ? marginy :'my-6','card_border card']" style="border-radius: 4px;">
        <h5 class="card-text m-2 title-card-border">
            {{ title }}
        </h5>
        <div :class="marginx">
            <slot name="content"/>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Título de la sección'
        },
        marginx:{
            type: String,
            default: 'mx-3'
        },
        marginy:{
            type:String,
            default:''
        }
    }
}
</script>

<style scoped>
.title-card-border{
	top: -17px;
	position: absolute; 
	background: white; 
	font-size: 12px;
}
</style>