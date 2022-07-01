<template>
    <v-navigation-drawer
        v-model="localDrawer"
        :width="width"
        @input="updateValue"
        temporary
        absolute
        right
    >
        <v-card flat elevation="0">
            <v-row>
                <v-col cols="12" class="d-flex justify-content-end pb-0 pr-7">
                    <v-btn
                        fab dark small
                        color="primary"
                        elevation="0"
                        @click="closeFilter">
                        <v-icon v-text="'mdi-close'"/>
                    </v-btn>
                </v-col>
            </v-row>
            <v-card-title class="pt-1 filter-title">
                {{ title }}
            </v-card-title>
            <DefaultDivider/>
            <div class="d-flex flex-column pt-0 px-7">
                <v-row>
                    <slot name="content"/>
                </v-row>
                <v-row>
                    <v-col cols="12" class="d-flex" style="justify-content: end; padding-right: 1.3rem !important;">
                        <DefaultButton
                            text
                            label="Limpiar filtros"
                            @click="cleanFilters"
                        />
                        <DefaultButton
                            label="Aplicar"
                            @click="filterData"
                            :disabled="disabledConfirmBtn"
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <slot name="consideraciones"/>
                </v-row>
            </div>
        </v-card>

    </v-navigation-drawer>
</template>
<script>
export default {
    props: {
        value: {
            type: Boolean,
            default: false
        },
        title: {
            type: String,
            default: 'Filtros'
        },
        width: {
            type: Number | String,
            default: '40%'
        },
        disabledConfirmBtn: {
            type: Boolean,
        }
    },
    data() {
        return {
            localDrawer: null
        }
    },
    created() {
        if (this.value) {
            this.localDrawer = this.value // set initial value
        }
    },
    watch: {
        value(val) {
            this.localDrawer = val // watch change from parent component
        }
    },
    methods: {
        updateValue(value) {
            let vue = this
            let section = document.getElementsByClassName('section-list')
            if (value)
                section[0].classList.add('overflow-hidden')
            else
                section[0].classList.remove('overflow-hidden')

            vue.$emit('input', value || null)
        },
        closeFilter() {
            let vue = this
            vue.$emit('input', false)
        },
        filterData() {
            let vue = this
            vue.$emit('filter')
        },
        cleanFilters(){
            let vue = this
            vue.$emit('cleanFilters')
        }
    }

}
</script>
