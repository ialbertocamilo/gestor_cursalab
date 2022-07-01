<template>

     <v-alert
            v-model="alert"
            color="error"
            dark
            border="top"
            transition="scale-transition"
            class="form-alert"
            dismissible
        >
            <v-row>
                <li v-for="(error, i) in errors" :key="i">
                    <span v-for="(row, j) in error.errors" :key="j">
                          Fila #{{ error.row }}: {{ row }}
                    </span>
                </li>
            </v-row>
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
