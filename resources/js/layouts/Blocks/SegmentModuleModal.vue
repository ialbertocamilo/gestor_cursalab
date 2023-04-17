<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <p class="description">
                        Selecciona los módulos para segmentar por criterios el curso.
                    </p>
                    <div class="modules-wrapper">
                        <div v-for="(module, i) of modules"
                            class="module">
                            <label>
                                <input
                                    v-model="modules[i].checked"
                                    type="checkbox"
                                    :value="module.id">
                                {{ module.value_text }}
                            </label>
                        </div>
                    </div>
                </v-col>
            </v-row>

        </template>
    </DefaultDialog>
</template>

<script>

export default {
    components: {

    },
    props: {
        options: {
            type: Object,
            required: true
        },
        criteria: {
            type: Array,
            requrired: true
        }
    },
    data () {
        return {
            resource: null,
            modules: []
        }
    },
    mounted() {

    },
    watch: {
        criteria: function (criteria) {
            if (criteria) {
                // Find module criteria, and add "checked"
                // property to each of its values, in order
                // be used with checkbox inputs

                const moduleCriteria = criteria.find(c => {
                    return c.code === 'module'
                })
                if (moduleCriteria) {
                    this.modules = moduleCriteria.values.map(m => {
                        return {
                            ...m,
                            checked: false
                        }
                    })
                }
            }
        }
    },
    methods : {
        onConfirm() {
            let vue = this

            let checkedModules = vue.modules.filter(m => m.checked);

            vue.$emit('onModulesSelected', checkedModules)
            //vue.$emit('onConfirm', true);
        },
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
    }
}

</script>

<style scoped>

.description {
   font-size: 14px;
}



.modules-wrapper {
    padding-top: 15px;
    padding-left: 15px;
}

.module {
    position: relative;
    margin-bottom: 20px;
}

label {
    color: #2A3649;
    font-size: 14px;
}

label input:before {
    position: absolute;
    left: -3px;
    top: 1px;
    display: inline-block;
    content: '';
    border: 1px solid #5458EA;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: white;
}

label input:checked:before {
    display: inline-block;
    content: '';
    border: 1px solid #5458EA;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #5458EA;
}

label input {
    border: none !important;
    margin-right: 15px;
}
</style>
