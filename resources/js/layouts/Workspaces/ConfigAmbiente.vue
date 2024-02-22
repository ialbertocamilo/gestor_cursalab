<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal" :showCardActions=false fullscreen>
            <template v-slot:content>
                <AmbientePage ref="AmbientePage" type="general"></AmbientePage>  
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import AmbientePage from '../../layouts/Ambiente/AmbientePage.vue'
export default {
    components: {AmbientePage},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
              
            },
            resource: {
               
            },
            selects: {
            },
            rules: {
            },
        };
    },
    watch: {
        search(val) {
            // Items have already been loaded
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }
            this.debounceTimer = setTimeout(() => {
                this.searchCourses(val);
            }, 800);
        },
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData() {
            let vue = this;
            if(vue.$refs['AmbientePage']){
                vue.$refs['AmbientePage'].loadData()
            }
            return 0;
        },
        async loadSelects() {
            let vue = this;
        },
    }
}
</script>
