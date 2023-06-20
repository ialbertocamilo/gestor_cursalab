<template>
    <section class="section-list">
        <v-card flat elevation="0">
            <v-card-title class="justify-content-between align-items-center position-relative">
                <span>Beneficios: {{ benefit_id ? 'Editar' : 'Crear' }}</span>
                <div class="box_btn_modules position-relative">
                    <v-menu
                        v-model="menu"
                            attach
                            offset-y
                            left
                            nudge-bottom="10"
						    :close-on-content-click="false"
                            min-width="auto"
                        >
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn
                                color="primary"
                                v-bind="attrs"
                                v-on="on"
                                class="btn_options_modules"
                                outlined
                            >
                                Agregar opción
                                <v-icon v-text="'mdi-chevron-down'"/>
                            </v-btn>
                        </template>

                        <v-list dense>
                                <v-list-item
                                    style="cursor: pointer;"
                                    v-for="action in options_modules"
                                    :key="action.code"
                                >
                                    <v-list-item-content class="py-0">
                                        <v-list-item-title class="d-flex justify-content-between py-2">

                                            <span>{{ action.name }}</span>
                                            <div class="bx_switch_options">
                                                <v-switch
                                                    class="default-toggle"
                                                    inset
                                                    label=""
                                                    hide-details="auto"
                                                    v-model="action.active"
                                                    @change="updateValue(action)"
                                                    dense
                                                ></v-switch>
                                            </div>

                                        </v-list-item-title>
                                    </v-list-item-content>

                                </v-list-item>
                            </v-list>
                    </v-menu>
                </div>
            </v-card-title>
        </v-card>
        <br>
        <v-card flat elevation="0">
            <v-card-text>
                <v-form ref="BenefitForm">
                    <DefaultErrors :errors="errors"/>

                    <v-row>
                        <v-col cols="7">
                            <DefaultInput
                                dense
                                label="Nombre"
                                placeholder="Ingrese un nombre"
                                v-model="resource.title"
                                :rules="rules.title"
                                show-required
                                counter="120"
                            />
                        </v-col>
                        <v-col cols="3">
                            <DefaultAutocomplete
                                show-required
                                :rules="rules.list_types"
                                dense
                                label="Tipo"
                                v-model="selectType"
                                :items="selects.list_types"
                                item-text="name"
                                item-value="code"
                                @onChange="changeTypes"
                            />
                        </v-col>
                        <v-col cols="2">
                            <DefaultToggle v-model="resource.active"/>
                        </v-col>
                    </v-row>
                    <v-row justify="center">
                        <v-col cols="6">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.imagen"
                                label="Imagen (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'imagen')"/>
                        </v-col>
                        <v-col cols="6">
                            <editor
                                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                v-model="resource.description"
                                :init="{
                                content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                height: 300,
                                menubar: false,
                                language: 'es',
                                force_br_newlines : true,
                                force_p_newlines : false,
                                forced_root_block : '',
                                plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                toolbar:
                                    'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                images_upload_handler: images_upload_handler,
                            }"/>

                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Cupo de participantes"
                                placeholder="Indicar cupos"
                                v-model="resource.cupo"
                                :rules="rules.cupo"
                                show-required
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter1'"
                                :options="modalDateFilter1"
                                v-model="resource.inicio_inscripcion"
                                label="Fecha de inicio de inscripción"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter2'"
                                :options="modalDateFilter2"
                                v-model="resource.fin_inscripcion"
                                label="Fecha de fin de inscripción"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter3'"
                                :options="modalDateFilter3"
                                v-model="resource.inicio_liberacion"
                                label="Fecha de liberación"
                                placeholder="Indicar fecha"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInput
                                dense
                                label="Correo de contacto"
                                placeholder="Indicar el correo aquí"
                                v-model="resource.correo"
                                :rules="rules.correo"
                                show-required
                            />
                        </v-col>
                    </v-row>

                    <!-- Sílabo -->
                    <v-row justify="space-around" v-if="options_modules[1].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Configuración del silabo"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_silabos">
                                        <v-row>
                                            <v-col cols="12" md="12" lg="12">
                                                    <draggable v-model="list_silabos" @start="drag_silabos=true" @end="drag_silabos=false" class="custom-draggable" ghost-class="ghost">
                                                        <transition-group type="transition" name="flip-list" tag="div">
                                                            <div v-for="(silabo, i) in list_silabos"
                                                                :key="silabo.id">
                                                                <div class="item-draggable activities">
                                                                    <v-row>
                                                                        <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                            </v-icon>
                                                                        </v-col>
                                                                        <v-col cols="7">
                                                                            <v-textarea
                                                                                rows="1"
                                                                                outlined
                                                                                dense
                                                                                auto-grow
                                                                                hide-details="auto"
                                                                                v-model="silabo.name"
                                                                                :class="{'border-error': silabo.hasErrors}"
                                                                            ></v-textarea>
                                                                        </v-col>
                                                                        <v-col cols="4" class="d-flex justify-content-center align-center">
                                                                            <div>
                                                                                <v-btn color="primary" outlined @click="silabo.expanded = !silabo.expanded">
                                                                                    <v-icon class="icon_size">mdi-plus</v-icon>
                                                                                    Asignar elementos
                                                                                </v-btn>
                                                                            </div>
                                                                            <div class="toggle_text_default mt_0">
                                                                                <DefaultToggle v-model="silabo.active"/>
                                                                            </div>
                                                                            <div>
                                                                                <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                        @click="eliminarSilabo(silabo, i)">
                                                                                    mdi-delete
                                                                                </v-icon>
                                                                            </div>
                                                                        </v-col>
                                                                    </v-row>
                                                                    <v-row v-if="silabo.expanded">
                                                                        <v-col cols="12">
                                                                            <editor
                                                                                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                                                                v-model="silabo.value"
                                                                                :init="{
                                                                                content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                                                                height: 200,
                                                                                menubar: false,
                                                                                language: 'es',
                                                                                force_br_newlines : true,
                                                                                force_p_newlines : false,
                                                                                forced_root_block : '',
                                                                                plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                                                                toolbar:
                                                                                    'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                                                                images_upload_handler: images_upload_handler,
                                                                            }"/>
                                                                        </v-col>
                                                                        <v-col cols="12">
                                                                            <div>Programación</div>
                                                                            <div class="row">
                                                                                <div class="col-3">
                                                                                    <DefaultInputDate
                                                                                        clearable
                                                                                        dense
                                                                                        :referenceComponent="'modalDateSilabo'"
                                                                                        :options="silabo.modalDateSilabo"
                                                                                        v-model="silabo.value_date"
                                                                                        label="Fecha"
                                                                                        placeholder="Fecha"
                                                                                    />
                                                                                </div>
                                                                                <div class="col-3">
                                                                                    <v-menu
                                                                                        ref="menu_time_silabo"
                                                                                        v-model="silabo.menu_value_time"
                                                                                        :close-on-content-click="false"
                                                                                        :nudge-right="40"
                                                                                        :return-value.sync="silabo.menu_value_time"
                                                                                        lazy
                                                                                        transition="scale-transition"
                                                                                        offset-y
                                                                                        max-width="290px"
                                                                                        min-width="290px"
                                                                                    >
                                                                                        <template v-slot:activator="{ on, attrs }">
                                                                                        <v-text-field
                                                                                            v-model="silabo.value_time"
                                                                                            label="Hora"
                                                                                            prepend-icon="mdi-clock"
                                                                                            dense
                                                                                            hide-details="auto"
                                                                                            readonly
                                                                                            v-bind="attrs"
                                                                                            v-on="on"
                                                                                        ></v-text-field>
                                                                                        </template>
                                                                                        <v-time-picker
                                                                                        v-if="silabo.menu_value_time"
                                                                                        v-model="silabo.value_time"
                                                                                        full-width
                                                                                        @click:minute="$refs.menu_time_silabo.save(silabo.value_time)"
                                                                                        ></v-time-picker>
                                                                                    </v-menu>
                                                                                </div>
                                                                            </div>
                                                                        </v-col>
                                                                    </v-row>
                                                                </div>
                                                            </div>
                                                        </transition-group>
                                                    </draggable>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <v-btn color="primary" outlined @click="addSilabo">
                                                    <v-icon class="icon_size">mdi-plus</v-icon>
                                                    Agregar nueva sección del silabo
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Sílabo -->
                    <!-- Links -->
                    <v-row justify="space-around" v-if="options_modules[3].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Link"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_links">

                                        <v-row>
                                            <v-col cols="12" md="12" lg="12">
                                                    <draggable v-model="list_links" @start="drag_links=true" @end="drag_links=false" class="custom-draggable" ghost-class="ghost">
                                                        <transition-group type="transition" name="flip-list" tag="div">
                                                            <div v-for="(link, i) in list_links"
                                                                :key="link.id">
                                                                <div class="item-draggable activities">
                                                                    <v-row>
                                                                        <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                            </v-icon>
                                                                        </v-col>
                                                                        <v-col cols="9">
                                                                            <v-textarea
                                                                                rows="1"
                                                                                outlined
                                                                                dense
                                                                                auto-grow
                                                                                hide-details="auto"
                                                                                v-model="link.name"
                                                                                :class="{'border-error': link.hasErrors}"
                                                                            ></v-textarea>
                                                                        </v-col>
                                                                        <v-col cols="1" class="d-flex align-center">
                                                                            <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                    @click="openModalAddLink(link, i)">
                                                                                mdi-link
                                                                            </v-icon>
                                                                        </v-col>
                                                                        <v-col cols="1" class="d-flex align-center">
                                                                            <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                    @click="eliminarLink(link, i)">
                                                                                mdi-delete
                                                                            </v-icon>
                                                                        </v-col>
                                                                    </v-row>
                                                                </div>
                                                            </div>
                                                        </transition-group>
                                                    </draggable>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <v-btn color="primary" outlined @click="addLink">
                                                    <v-icon class="icon_size">mdi-plus</v-icon>
                                                    Agregar nuevo link
                                                </v-btn>
                                            </v-col>
                                        </v-row>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Links -->
                    <!-- Map -->
                    <v-row justify="space-around" v-if="options_modules[2].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Ubicación"
                            >
                                <template slot="content">
                                    <v-row justify="center" class="align-items-center">
                                        <v-col cols="8">
                                            <div class="box_search_direction_map">
                                                <span class="lbl_search_direction">Dirección</span>
                                                <GmapAutocomplete ref="autocompleteMap" :position.sync="markers[0].position" @place_changed="setPlace" class="custom-default-input" placeholder="Ingresa la dirección donde se realizara el curso"/>
                                            </div>
                                        </v-col>
                                        <v-col cols="4" class="d-flex justify-content-center align-items-center bx_benefit_accesible">
                                            <DefaultToggle
                                                v-model="resource.discapacidad"
                                                active-label="Accesible para usuarios con discapacidad"
                                                inactive-label="Accesible para usuarios con discapacidad"
                                            />
                                        </v-col>
                                    </v-row>
                                    <div class="row">
                                        <v-col cols="12">
                                            <div class="bx_maps_benefit" id="bx_maps_benefit">
                                                <GmapMap
                                                    :center="center"
                                                    :zoom="zoom"
                                                    style="height: 300px"
                                                    >
                                                    <GmapMarker
                                                        :key="index"
                                                        v-for="(m, index) in markers"
                                                        :position="m.position"
                                                        @click="center = m.position"
                                                        :draggable="true"
                                                        @drag="updateCoordinates"
                                                    />
                                                </GmapMap>
                                            </div>
                                        </v-col>
                                    </div>
                                    <v-row>
                                        <v-col cols="12">
                                            <DefaultTextArea
                                                label="Referencia"
                                                placeholder="Ingresa una referencia de como llegar al lugar donde se realizara el curso"
                                                v-model="resource.referencia"
                                                :rules="rules.referencia"
                                            />
                                        </v-col>
                                    </v-row>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Map -->
                    <!-- Dificultad -->
                    <v-row justify="space-around" v-if="options_modules[5].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Dificultad del beneficio"
                            >
                                <template slot="content">
                                    <div class="box_dificultad_beneficios">
                                        <p>Selecciona el nivel de dificultad que tendra el beneficio</p>
                                        <div class="box_items_dificultad d-flex justify-content-center">
                                            <div class="item_dificultad" :class="{'active': activeDificultad == 'basico'}" @click="selectDificultad('basico')">Básico</div>
                                            <div class="item_dificultad" :class="{'active': activeDificultad == 'intermedio'}" @click="selectDificultad('intermedio')">Intermedio</div>
                                            <div class="item_dificultad" :class="{'active': activeDificultad == 'avanzado'}" @click="selectDificultad('avanzado')">Avanzado</div>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Dificultad -->
                    <!-- Duración -->
                    <v-row justify="space-around" v-if="options_modules[7].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Duración"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_duracion d-flex justify-content-center align-items-center">
                                        <div class="box_input_duracion mr-6">
                                            <DefaultInput
                                                dense
                                                label="Duración del beneficio (hrs)"
                                                placeholder="Ingresar la duración en horas"
                                                v-model="duracionValue"
                                                :rules="rules.duracion"
                                                @input="duracionIlimitado = null"
                                            />
                                        </div>
                                        <div class="box_button_duracion">
                                            <v-radio-group v-model="duracionIlimitado">
                                                <v-radio name="duracion" label="Ilimitado" :value="'ilimitado'" @change="duracionValue = null"></v-radio>
                                            </v-radio-group>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Duración -->
                    <!-- Encuesta -->
                    <v-row justify="space-around" v-if="options_modules[8].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Encuesta"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_encuesta d-flex justify-content-center">
                                        <div class="box_input_encuesta">
                                            <DefaultAutocomplete
                                                :rules="rules.lista_encuestas"
                                                dense
                                                label="Encuesta"
                                                placeholder="Agrega una encuesta"
                                                v-model="resource.lista_encuestas"
                                                :items="selects.lista_encuestas"
                                                item-text="name"
                                                item-value="id"
                                            />
                                        </div>
                                        <div class="box_button_encuesta">
                                            <v-btn color="primary" outlined @click="addLinkExterno">
                                                <div class="img mr-1"><img src="/img/benefits/icono_link.svg"></div>
                                                Link externo
                                            </v-btn>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Encuesta -->
                    <!-- Promotor -->
                    <v-row justify="space-around" v-if="options_modules[0].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Promotor del beneficio"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_promotor d-flex">
                                        <div class="box_input_promotor">
                                            <DefaultInput
                                                dense
                                                label="Promotor"
                                                placeholder="Empresa que promociona"
                                                v-model="resource.promotor"
                                                :rules="rules.promotor"
                                            />
                                        </div>
                                        <div class="box_button_promotor">
                                            <v-btn color="primary" outlined @click="addLogoPromotor">
                                                Agregar Logotipo
                                            </v-btn>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Promotor -->
                    <!-- Speaker -->
                    <v-row justify="space-around" v-if="options_modules[4].active">
                        <v-col cols="12">
                            <DefaultModalSection
                                title="Speaker"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_speaker d-flex">
                                        <div class="box_input_speaker">
                                            <div v-if="!resource.speaker" class="d-flex align-center">
                                                <div class="bx_speaker_img"></div>
                                                <div class="bx_speaker_name">
                                                    <span>Seleccionar speaker</span>
                                                </div>
                                            </div>
                                            <div v-if="resource.speaker" class="d-flex align-center">
                                                <div class="bx_speaker_img">
                                                    <img src="/img/benefits/sesion_presencial.svg">
                                                </div>
                                                <div class="bx_speaker_name">
                                                    <span>{{resource.speaker.name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box_button_speaker">
                                            <v-btn color="primary" outlined @click="openModalSelectSpeaker">
                                                Seleccionar speaker
                                            </v-btn>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Speaker -->

                </v-form>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    :loading="loadingActionBtn"
                />
            </v-card-actions>
        </v-card>
        <ModalAddLink
            :ref="modalAddLink.ref"
            v-model="modalAddLink.open"
            :data="modalAddLink.data"
            width="650px"
            @closeModalAddLink="modalAddLink.open = false"
            @confirmAddLink="confirmAddLink"
            />
        <ModalSelectSpeaker
            :ref="modalSelectSpeaker.ref"
            v-model="modalSelectSpeaker.open"
            :data="modalSelectSpeaker.data"
            width="650px"
            @closeModalSelectSpeaker="modalSelectSpeaker.open = false"
            @confirmSelectSpeaker="confirmSelectSpeaker"
            />
    </section>
</template>
<script>
const fields = [
    'title', 'active', 'position', 'imagen',
    'type_id',
    'description'
];
const file_fields = ['imagen', 'plantilla_diploma'];

import DialogConfirm from "../../components/basicos/DialogConfirm";
import Editor from "@tinymce/tinymce-vue";
import GmapMap from 'vue2-google-maps/dist/components/map.vue'
import ModalAddLink from "../../components/Benefit/ModalAddLink";
import ModalSelectSpeaker from "../../components/Benefit/ModalSelectSpeaker";

export default {
    components: {DialogConfirm, Editor,GmapMap, ModalAddLink, ModalSelectSpeaker},
    props: [ 'benefit_id', 'api_key_maps'],
    data() {
        return {
            // para el mapa
            center: { lat: -12.0529046, lng: -77.0253457 },
            zoom: 16,
            currentPlace: null,
            markers: [{
                position: { lat: -12.0529046, lng: -77.0253457 }
            }],
            // para el desplegable
            menu: false,
            options_modules: [
                {name: 'Promotor del beneficio', code: 'promotor', active: false},
                {name: 'Sílabo', code: 'silabo', active: false},
                {name: 'Ubicación / Mapa', code: 'ubicacion', active: false},
                {name: 'Agregar link', code: 'links', active: false},
                {name: 'Speaker', code: 'speaker', active: false},
                {name: 'Dificultad de cursos', code: 'dificultad', active: false},
                {name: 'Implementos necesarios', code: 'implementos', active: false},
                {name: 'Duración', code: 'duracion', active: false},
                {name: 'Encuesta', code: 'encuesta', active: false},
            ],
            // modal add link

            modalAddLink: {
                ref: 'ModalAddLink',
                open: false,
                data: {},
                endpoint: '',
            },
            // modal speaker

            modalSelectSpeaker: {
                ref: 'modalSelectSpeaker',
                open: false,
                data: [],
                endpoint: '',
            },

            // otros
            drag_links: false,
            drag_silabos: false,
            list_links: [],
            list_silabos: [],
            selectType: null,
            activeDificultad: null,
            duracionValue: null,
            duracionIlimitado: null,
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
            },
            modalDateFilter3: {
                open: false,
            },
            // silabo time
            silabo_time: null,
            silabo_menu2: false,
            silabo_modal2: false,
            //
            date: null,
            date2: null,
            date3: null,
            url: window.location.search,
            errors: [],
            conf_focus: true,
            base_endpoint: '/beneficios',
            resourceDefault: {
                title: null,
                description: null,
                // position: null,
                imagen: null,
                file_imagen: null,
                active: true,
                type_id: null,
                inicio_inscripcion: null,
                fin_inscripcion: null,
                inicio_liberacion: null,
                correo: null,
                list_types: [],
                lista_encuestas: [],
                dificultad: null,
            },
            resource: {},
            rules: {
                title: this.getRules(['required', 'max:120']),
                list_types: this.getRules(['required']),
                cupo: this.getRules(['number']),
                inicio_inscripcion: this.getRules(['required']),
                fin_inscripcion: this.getRules(['required']),
                inicio_liberacion: this.getRules(['required']),
            },
            selects: {
                lista_encuestas: [],
                list_types: [],
            },
            loadingActionBtn: false,
            courseValidationModal: {
                ref: 'CursoValidacionesModal',
                open: false,
                title_modal: 'El curso es prerrequisito',
                type_modal:'requirement',
                content_modal: {
                    requirement: {
                        title: '¡El curso que deseas desactivar es un prerrequisito!'
                    },
                }
            },
            courseValidationModalDefault: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
                persistent: false,
                showCloseIcon: true,
                type: null
            },
            courseUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de un <b>curso</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios no podrán acceder al curso.',
                            'El diploma del curso no aparecerá para descargar desde el app.',
                            'No podrás ver el curso como opción para la descarga de reportes.',
                            'El detalle del curso activos/inactivos aparecerá en “Notas de usuario”.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios ahora podrán acceder al curso.',
                            'El diploma del curso ahora aparecerá para descargar desde el app.',
                            'Podrás ver el curso como opción para descargar reportes.'
                        ]
                    }
                },
            },
        }
    },
    computed: {
    },
    async mounted() {
        this.showLoader()
        await this.loadData()
        this.hideLoader()
    },
    methods: {

        async openModalSelectSpeaker() {
            let vue = this;

            vue.showLoader();

            vue.modalSelectSpeaker.open = true

                await vue.$http.get(`/beneficios/speakers/search`)
                    .then((res) => {
                        let res_speakers = res.data.data.data;
                        console.log(res);
                        console.log(res_speakers);
                        vue.modalSelectSpeaker.data = res_speakers
                        this.hideLoader()
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
        },
        confirmSelectSpeaker( value ){
            let vue = this;
            console.log(value);
            console.log(vue.resource);
            vue.resource.speaker = value
            vue.modalSelectSpeaker.open = false
        },
        confirmAddLink( value ) {
            let vue = this;
            console.log(value);
            console.log(this.list_links);
            console.log(vue.resource);
            vue.modalAddLink.open = false
        },
        async openModalAddLink( link ) {
            let vue = this
            vue.modalAddLink.open = true
            vue.modalAddLink.data = link
        },
        addLink() {
            let vue = this;
            const newID = `n-${Date.now()}`;
            const newLink = {
                id: newID,
                name: "",
                value: "",
                active: 1,
                benefit_id: vue.resource.id,
                hasErrors: false,
                is_default:false
            };
            console.log(newLink);
            console.log(vue.resource);
            vue.list_links.unshift(newLink);
        },
        eliminarLink(link, index) {
            let vue = this;
            console.log(vue.resource);
                vue.list_links.splice(index, 1);
            console.log(vue.resource);
        },
        addSilabo() {
            let vue = this;
            const newID = `n-${Date.now()}`;
            const newSilabo = {
                id: newID,
                name: "",
                value: "",
                active: 1,
                benefit_id: vue.resource.id,
                hasErrors: false,
                is_default:false,
                expanded: false,
                modalDateSilabo: {
                    open: false,
                },
            };
            console.log(newSilabo);
            console.log(vue.resource);
            vue.list_silabos.unshift(newSilabo);
        },
        eliminarSilabo(silabo, index) {
            let vue = this;
            console.log(vue.resource);
                vue.list_silabos.splice(index, 1);
            console.log(vue.resource);
        },
        updateValue(value) {
            let vue = this
            console.log(vue.options_modules);
        },
        changeTypes() {
            console.log("sss");
        },
        setPlace(place) {
            this.currentPlace = place;
            console.log(this.currentPlace);
            if (this.currentPlace) {
                const marker = {
                lat: this.currentPlace.geometry.location.lat(),
                lng: this.currentPlace.geometry.location.lng(),
                };
                this.markers = [{ position: marker }];
                this.center = marker;
                this.currentPlace = null;
            }
        },
        updateCoordinates(location) {
            let geocoder = new google.maps.Geocoder()
            geocoder.geocode({ 'latLng': location.latLng }, (result, status) => {
                if (status ===google.maps.GeocoderStatus.OK) {
                    this.$refs.autocompleteMap.$refs.input.value = result[0].formatted_address
                }
            })
        },
        images_upload_handler(blobInfo, success, failure) {
            // console.log(blobInfo.blob());
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            axios
                .post("/upload-image/beneficios", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        closeModal() {
            let vue = this
            window.location.href = vue.base_endpoint;
        },
        confirmModal(validateForm = true) {
            let vue = this
            vue.errors = []

            if( vue.duracionIlimitado == 'ilimitado' ) {
                vue.resource.duracion = 'ilimitado'
            }
            else if( vue.duracionValue != null && vue.duracionValue != '' ) {
                vue.resource.duracion = vue.duracionValue
            }

            vue.resource.type = this.selectType
            vue.resource.list_links = this.list_links
            console.log(vue.resource);
            vue.loadingActionBtn = true
            vue.showLoader()
            const validForm = vue.validateForm('BenefitForm')

            if (!validForm || !vue.isValid()) {
                this.hideLoader()
                vue.loadingActionBtn = false
                return
            }

            const edit = vue.benefit_id !== ''
            let url = `${vue.base_endpoint}/${edit ? `update/${vue.benefit_id}` : 'store'}`
            let method = edit ? 'PUT' : 'POST';

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1" : "0");

            vue.$http.post(url, formData)
                .then(async ({data}) => {
                    console.log(data);
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    vue.showAlert(data.data.msg)
                    setTimeout(() => vue.closeModal(), 2000)
                })
                .catch(error => {
                    if (error && error.errors){
                        vue.errors = error.errors
                    }
                    console.log(error);
                    vue.loadingActionBtn = false
                })
        },
        async loadData() {
            let vue = this
            let params = this.getAllUrlParams(window.location.search);

            if( params.type ) {
                vue.selectType = params.type
            }

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.base_endpoint}/form-selects`
            await vue.$http.get(url)
                .then(({data}) => {
                    let response = data.data ? data.data : data;
                    vue.selects.lista_encuestas = response.polls
                    vue.selects.list_types = response.types_benefit
                })
            if(vue.benefit_id != '') {
                let url = `${vue.base_endpoint}/search/${vue.benefit_id}`
                await vue.$http.get(url)
                    .then(({data}) => {
                        console.log(data);
                        let response = data.data.data;
                        console.log(this.resource);
                        console.log(response);
                        vue.resource = Object.assign({}, response)
                    })
            }
            return 0;
        },
        isValid() {

            let valid = true;
            let errors = [];

            if (this.selectType == null || this.selectType == '') {
                errors.push({
                    message: 'Debe seleccionar un tipo de beneficio'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        },
        async selectDificultad( item ) {
            let vue = this
            vue.resource.dificultad = item
            vue.activeDificultad = item
            console.log(vue.activeDificultad);
            console.log(vue.resource);
            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })
        },
        addLogoPromotor() {

        },
        addLinkExterno() {
            let vue = this;
            const newID = `n-${Date.now()}`;
            const newLink = {
                id: newID,
                name: "",
                value: "",
                active: 1,
                benefit_id: vue.resource.id,
                hasErrors: false,
                is_default:false
            };
            this.openModalAddLink(newLink)
        }
    }
}

</script>
<style lang="scss">
.bx_maps_benefit {
    height: 300px;
    width: 100%;
}
.bx_benefit_accesible .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: 0 !important;
}
.box_dificultad_beneficios p {
    font-family: 'open sans';
    font-size: 20px;
    color: #9E9E9E;
    text-align: center;
}
.box_items_dificultad .item_dificultad {
    border: 1px solid #D9D9D9;
    margin: 0 10px;
    padding: 10px 20px;
    min-width: 150px;
    text-align: center;
    color: #D9D9D9;
    font-weight: 600;
    font-family: 'open sans';
    border-radius: 4px;
    cursor: pointer;
}
.box_items_dificultad .item_dificultad.active,
.box_items_dificultad .item_dificultad:hover {
    border: 1px solid #5457E7;
    color: #5457E7;
}
.box_input_promotor {
    flex: 1;
    margin-right: 15px;
}
.box_input_duracion,
.box_input_encuesta {
    min-width: 600px;
    margin-right: 10px;
}
.box_button_encuesta button .img img {
    max-width: 20px;
}
.box_button_duracion .v-input__control .v-messages {
    display: none;
}
.box_button_duracion .v-input__control .v-input__slot,
.box_button_duracion .v-input__control .v-input__slot .v-radio .v-label {
    margin-bottom: 0;
    font-family: 'open sans';
    font-size: 14px;
    color: #2A3649;
}
.box_search_direction_map {
    border: 1px solid #D9D9D9;
    border-radius: 5px;
    position: relative;
}
.box_search_direction_map span.lbl_search_direction {
    position: absolute;
    top: -8px;
    left: 9px;
    font-size: 11.5px;
    line-height: 1;
    background: #fff;
    padding: 0 2px;
}
.box_search_direction_map input {
    width: 100%;
    padding: 10px 15px;
}
.bx_benefit_accesible .v-input__slot .v-label {
    font-size: 13px;
    font-family: "Nunito", sans-serif;
    color: #2A3649;
}
.box_btn_modules {
    min-width: 300px;
    text-align: right;
}
.bx_switch_options {
    margin-left: 20px;
}
.bx_switch_options .v-input.default-toggle {
    margin: 0 !important;
}

.toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}
.toggle_text_default.mt_0 .v-input.default-toggle {
    margin: 0 10px !important;
}
.box_input_speaker {
    width: 100%;
    border: 1px solid #D9D9D9;
    padding: 6px 15px;
    display: flex;
    align-items: center;
    margin-right: 10px;
    border-radius: 4px;
    .bx_speaker_img {
        background: #D9D9D9;
        width: 30px;
        height: 30px;
        display: inline-flex;
        border-radius: 50%;
        margin-right: 10px;
    }
}
.box_button_speaker button {
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    height: 44px !important;
}
</style>
