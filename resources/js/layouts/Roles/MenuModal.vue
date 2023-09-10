<template>
    <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
        <template v-slot:content>
            <v-row justify="center">
                <v-col cols="12" class="item-draggable d-flex justify-content-end">
                        <a target="_BLANK" href="https://pictogrammers.github.io/@mdi/font/2.0.46/">Iconos 1</a>
                        <a class="ml-4" target="_BLANK" href="https://www.w3schools.com/icons/icons_reference.asp">Iconos 2</a>
                </v-col>
                <v-col cols="12" class="item-draggable d-flex justify-content-center">
                    <DefaultButton label="Añadir menú" icon="mdi-plus" :outlined="true" @click="addMenu()" />
                </v-col>
                <v-col cols="12">
                    <draggable v-model="menus" @end="onDragEnd" class="custom-draggable" ghost-class="ghost">
                        <transition-group type="transition" name="flip-list" tag="div">
                            <div v-for="(menu, menu_index) in menus" :key="menu_index + 1">

                                <div class="item-draggable list d-flex align-items-baseline" v-if="!menu.edit">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <v-icon class="mr-2">{{ menu.icon }}</v-icon>
                                            <div class="mr-2" v-text="menu.name"></div>
                                            <div class="mr-2" v-text="(menu.is_beta) ? '(Beta)' : ''"></div>
                                            <div class="mr-2" v-text="(menu.show_upgrade) ? '(Upgrade)' : ''"></div>
                                            <div class="mr-2 menu-description">
                                                <!-- <v-edit-dialog save-text="Guardar" cancel-text="Cancelar" large v-model="menu.description">
                                                    <div class="menu-description"> -->
                                                        Descripción: {{ menu.description }}
                                                    <!-- </div>
                                                    <template v-slot:input> -->
                                                        
                                                        <!-- <v-text-field v-model="menu.description" label="Edit"
                                                            single-line></v-text-field> -->
                                                    <!-- </template>
                                                </v-edit-dialog> -->
                                            </div>
                                        </div>
                                        <v-spacer></v-spacer>
                                        <v-btn class="btn btn-md" text icon color="primary"
                                            @click="editFunctionality(menu_index, true, menu.name)">
                                            <v-icon>mdi mdi-pencil</v-icon>
                                        </v-btn>

                                        <v-btn v-if="menu.children.length > 0" class="btn btn-md" text icon color="primary"
                                            @click="expandMenu(menu, menu_index)">
                                            <v-icon>mdi mdi-grid</v-icon>
                                        </v-btn>
                                        <v-btn v-if="menu.children.length == 0" class="btn btn-md" text icon color="primary"
                                            @click="deleteMenu(menu)">
                                            <v-icon>mdi mdi-delete</v-icon>
                                        </v-btn>
                                </div>
                                <div v-else>
                                    <div class="item-draggable list d-flex align-items-baseline" e>
                                        <v-text-field class="mx-2" dense v-model="menu.icon" label="Icono" autocomplete="off" />
                                        <v-text-field dense class="mx-2" v-model="menu.name" requred autocomplete="off"
                                            label="Título" />
                                        <v-checkbox class="mx-2" v-model="menu.is_beta" label="Es beta"></v-checkbox>
                                        <v-checkbox class="mx-2" v-model="menu.show_upgrade"
                                            label="Mostrar upgrade"></v-checkbox>
    
                                        <v-btn class="btn btn-md" text icon color="primary"
                                            @click="editFunctionality(menu_index, false, menu.name)">
                                            <v-icon>mdi mdi-close</v-icon>
                                        </v-btn>
                                    </div>
                                    <div class="col-md-12">
                                        <DefaultRichText
                                            :key="menu_index"
                                            v-model="menu.description"
                                            label="Descripción"
                                            maxLength="2000"
                                        />
                                    </div>
                                </div>
                                <transition name="fade">
                                    <ul v-if="menu.expanded" class=" ml-8">
                                        <draggable v-model="menu.children" @end="onDragSubmenuEnd(menu)">
                                            <li v-for="(submenu) in menu.children" :key="submenu.id"
                                                class="list item-draggable py-3 d-flex">
                                                <v-edit-dialog :return-value.sync="submenu.icon">
                                                    <v-icon class="mr-2">{{ submenu.icon }}</v-icon>
                                                    <template v-slot:input>
                                                        <v-text-field v-model="submenu.icon" label="Edit"
                                                            single-line></v-text-field>
                                                    </template>
                                                </v-edit-dialog>
                                                <v-edit-dialog :return-value.sync="submenu.name">
                                                    {{ submenu.name }}
                                                    <template v-slot:input>
                                                        <v-text-field v-model="submenu.name" label="Edit"
                                                            single-line></v-text-field>
                                                    </template>
                                                </v-edit-dialog>
                                                <v-checkbox class="mx-2" v-model="submenu.is_beta"
                                                    label="Es beta"></v-checkbox>
                                                <v-checkbox class="mx-2" v-model="submenu.show_upgrade"
                                                    label="Mostrar upgrade"></v-checkbox>
                                                <v-menu offset-y>
                                                    <template v-slot:activator="{ on, attrs }">
                                                        <v-btn color="primary" text icon v-bind="attrs" v-on="on">
                                                            <v-icon>mdi-cursor-move</v-icon>
                                                        </v-btn>
                                                    </template>
                                                    <v-list>
                                                        <v-list-item
                                                            v-for="(listmenu, submenu_index) in menus.filter(m => m.id != menu.id)"
                                                            :key="submenu_index">
                                                            <v-list-item-title style="cursor:pointer"
                                                                @click="moveSubMenu(listmenu, menu, submenu)">
                                                                {{ listmenu.name }}
                                                            </v-list-item-title>
                                                        </v-list-item>
                                                    </v-list>
                                                </v-menu>
                                            </li>
                                            <v-spacer></v-spacer>
                                        </draggable>
                                    </ul>
                                </transition>
                            </div>
                        </transition-group>
                    </draggable>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>
<script>
import draggable from 'vuedraggable'
import DefaultRichText from "../../components/globals/DefaultRichText";

export default {
    components: { draggable,DefaultRichText },
    props: {
        options: {
            type: Object,
            required: true,
        },
        width: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            menus: []
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        resetSelects() {
            let vue = this
        },
        async confirmModal() {
            let vue = this
            this.showLoader()

            await vue.$http.post(`/menus/update`, vue.menus)
                .then((res) => {
                    this.hideLoader()
                    vue.$emit('onCancel')
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
        },
        async loadData() {
            let vue = this
            vue.menus = [];
            vue.showLoader();
            await vue.$http.get(`/menus/list`)
                .then(({ data }) => {
                    vue.menus = data.data.map((d) => {
                        d.expanded = false;
                        return d;
                    });
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
        },
        loadSelects() {
            let vue = this
        },
        editFunctionality(index, val, name) {
            let vue = this;
            vue.$nextTick(() => {
                vue.menus[index].edit = val;
                vue.menus[index].expanded = false;
                vue.menus[index].name = '';
                vue.menus[index].name = name;
            });
        },
        addMenu() {
            let vue = this;
            vue.menus.push({
                id: null,
                name: '',
                icon: '',
                is_beta: false,
                show_upgrade: false,
                position: vue.menus.length + 1,
                edit: true,
                children: []
            });
        },
        expandMenu({ name }, index) {
            this.$nextTick(() => {
                this.menus[index].expanded = !this.menus[index].expanded;
                this.menus[index].name = '';
                this.menus[index].name = name;
            });
        },
        onDragEnd(event) {
            this.menus.forEach((menu, index) => {
                menu.position = index + 1;
            });
        },
        onDragSubmenuEnd(menu_index) {
            this.updateSubMenusPosition(this.menus.findIndex((m) => m.id == menu_index.id));
        },
        moveSubMenu(new_menu, old_menu, submenu) {
            const oldIndex = this.menus.findIndex((m) => m.id == old_menu.id);
            const newIndex = this.menus.findIndex((m) => m.id == new_menu.id);
            const oldIndexSubmenu = this.menus[oldIndex].children.findIndex((s) => s.id == submenu.id);
            this.menus[oldIndex].children.splice(oldIndexSubmenu, 1)[0];
            submenu.parent_id = this.menus[newIndex].id;
            this.menus[newIndex].children.push(submenu);
            this.updateSubMenusPosition(oldIndex);
            this.updateSubMenusPosition(newIndex);
        },
        updateSubMenusPosition(indexMenu) {
            this.menus[indexMenu].children.forEach((submenu, index) => {
                submenu.position = index + 1;
            });
        },deleteMenu(menu){
            const index = this.menus.findIndex((m) => m.id == menu.id);
            this.menus.splice(index, 1)[0];
        }
    }
}
</script>
<style>
.custom-draggable,
.custom-draggable span {
    width: 100%;
}

.item-draggable.list {
    padding: .75rem 1.25rem;
    background-color: #fff;
    border: 1px solid #ebebeb;
    transition: border-color 0.3s;
}

.list .v-label {
    margin: 0;
}

.list .v-messages {
    display: none;
}

.item-draggable.list:hover {
    border-color: #4287f5;
    /* Cambiar el color del borde al pasar el mouse */
}

.item-draggable.list textarea,
.no-white-space .v-select__selection--comma,
.item-draggable.list .toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}

.item-draggable.list textarea {
    font-size: 13px;
}

.item-draggable.list .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: initial !important;
}

.expandable-list {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.3s ease-in-out;
}

.expanded {
    max-height: 500px;
    /* Ajusta la altura máxima deseada */
    transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
    opacity: 1;
}
.menu-description{
    width: 200px; 
    white-space: nowrap;
    overflow: hidden; 
    text-overflow: ellipsis; 
}
</style>
