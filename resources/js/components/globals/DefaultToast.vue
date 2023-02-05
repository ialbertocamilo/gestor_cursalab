<template>
    <div
        :style="{
            'background-color': background,
            'border-color': borderColor,
            'bottom': bottom }"
        class="default-toast">
        <v-icon
            v-if="icon"
            :color="iconColor"
            v-text="icon"></v-icon>
        <div class="text-wrapper">
            <span v-html="text" class="text"></span>
            <a  v-if="buttonText"
                href="javascript:"
                class="ml-1"
                @click="() => { buttonAction(); close(); }">
                {{ buttonText }}
            </a>
        </div>
        <v-icon
            @click="close()"
            small
            :color="iconColor"
            class="ml-1 mb-1">
            mdi-close
        </v-icon>
    </div>
</template>

<script>
export default {
    props: {

        icon: {
            type: String,
            default: 'mdi-information'
        },
        iconColor: {
            type: String,
            default: '#1A2128'
        },
        delay: {
            type: Number,
            default: 10000
        },
        background: {
            type: String,
            defaut: '#FFC225'
        },
        borderColor: {
            type: String,
            default: 'transparent'
        },
        bottom: {
            type: String,
            default: '90px',
        },
        text: String,
        text_color: {
            default:'#1A2128',
        },
        buttonText: {
            type: String,
            default: ''
        },
        buttonAction: {
            type: Function,
            default: () => {}
        }
    },
    watch: {

    },
    mounted: function () {
        const vue = this
        setTimeout(() => {
            this.$emit('delay-finished')
        }, vue.delay)
    },
    methods: {
        close : function () {
            this.$emit('close')
        }
    }
}
</script>

<style scoped>
.default-toast {
    position: fixed;
    display: flex;
    align-items: center;
    z-index: 999;
    min-width: 431px;
    padding-left: 15px;
    padding-right: 15px;
    height: 45px;
    right: 90px;
    border: 1px solid transparent;
    border-radius: 10px;
}

.default-toast .text-wrapper {
    display: block;
    flex-grow: 2;
}

.default-toast .text {
    text-align: left;
    font-weight: 700 !important;
    font-size: 14px !important;
}

</style>
<style>
.default-toast a,
.default-toast a:hover {
    color: white !important;
    text-decoration: underline !important;
}
</style>
