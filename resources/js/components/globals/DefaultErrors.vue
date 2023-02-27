<template>

     <v-alert
            v-model="alert"
            color="error"
            dark
            border="top"
            transition="scale-transition"
            dismissible
            class="form-alert"
        >
            <ul class="mb-0" v-for="(error, i) in errors" :key="i">
                <li v-for="(row, j) in error" :key="j">
                    {{ row }}
                </li>
            </ul>
        </v-alert>
</template>


<script>
export default {
    props: {
        errors: {
            type: [Object, Array],
            default: []
        }
    },

    watch: {
        errors(val) {
            if (val && Object.keys(val).length > 0)
            {
                this.alert = true // watch change from parent component
            }else{
                this.alert = false
            }
        }
    },
    data () {
        return {
            alert: false,
        }
    },
}
</script>
