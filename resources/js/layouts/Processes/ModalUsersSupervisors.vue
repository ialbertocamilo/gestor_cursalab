<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModal"
        :class="{}"
        content-class="br-dialog dialog_users_supervisors"
    >
        <v-card>
            <span class="title_modal text_default">Listado de supervisores y sus colaboradores</span>
            <div class="bx_close">
                <v-btn icon :ripple="false" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8 text-center">
                <div class="d-flex justify-content-between">
                    <div class="bx_list_supervisors">
                        <span class="text_default fw-bold mb-2">Supervisores</span>
                        <div class="bx_scroll">
                            <div class="item_supervisors selected" v-for="(item, index) in list_supervisors" :key="index" @click="selectSupervisor(item)">
                                {{ item.name }}
                            </div>
                        </div>
                    </div>
                    <div class="bx_list_users">
                        <span class="text_default fw-bold mb-2">Colaboradores asignados</span>
                        <div class="bx_scroll">
                            <div class="item_users" v-for="(item, index) in list_users" :key="index">
                                {{ item.name }}
                            </div>
                        </div>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props:{
        value: null,
        width: null,
        list_supervisors: []
    },
    data() {
        return {
            list_users: [],
        };
    },
    methods: {
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        async confirmModal() {
            let vue = this
        },
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetSelects() {
            let vue = this
        },
        resetValidation() {
            let vue = this
        },
        selectSupervisor(value) {
            let vue = this
            // vue.$emit('selectActivityModal', value)
            if(value.users.length > 0) {
                vue.list_users = value.users
            }
        },
        async loadData(resource) {
            let vue = this
        },
        async loadSelects() {
            let vue = this;
        },

    },
};
</script>

<style lang="scss">
.dialog_users_supervisors {
    .title_modal {
        margin-top: 20px;
        margin-left: 30px;
        font-size: 15px;
    }
    .bx_close {
        position: absolute;
        right: 10px;
        top: 10px;
    }
    .bx_list_supervisors {
        flex: 1;
        text-align: left;
        padding: 11px;
        padding-right: 0;
        .item_supervisors {
            border-radius: 20px 0 0 20px;
            padding: 10px 20px;
            cursor: pointer;
            &.selected {
                background-color: #5459E8;
                color: #fff;
            }
        }
    }
    .bx_list_users {
        flex: 1;
        border: 1px solid #5757EA;
        border-radius: 4px;
        text-align: left;
        padding: 10px;
        min-height: 200px;
        .item_users {
            display: list-item;
            margin-left: 20px;
            margin-bottom: 3px;
        }
    }
}
</style>
