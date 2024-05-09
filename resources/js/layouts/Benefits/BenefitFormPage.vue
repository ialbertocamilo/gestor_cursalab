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
                            >
                                Agregar más configuraciones
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
                                v-model="resource.image"
                                label="Imagen (500x350px)"
                                :file-types="['image']"
                                @onSelect="setFile($event, resource,'image')"/>
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
                            <div class="box_beneficio_duracion d-flex justify-content-center align-items-center">
                                <div class="box_input_duracion mr-6">
                                    <DefaultInput
                                        dense
                                        label="Cupo de participantes"
                                        placeholder="Indicar cupos"
                                        v-model="cupoValue"
                                        @input="cupoIlimitado = null"
                                        type="number"
                                    />
                                </div>
                                <div class="box_button_duracion">
                                    <v-radio-group v-model="cupoIlimitado">
                                        <v-radio name="cupo" label="Ilimitado" :value="'ilimitado'" @change="cupoValue = null"></v-radio>
                                    </v-radio-group>
                                </div>
                            </div>
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
                                show-required
                                :min="new Date().toISOString().substr(0, 10)"
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultInputDate
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter2'"
                                :options="modalDateFilter2"
                                v-model="resource.fin_inscripcion"
                                label="Cierre de inscripción"
                                placeholder="Indicar fecha"
                                show-required
                                :min="minDate(resource.inicio_inscripcion)"
                                :disabled="resource.inicio_inscripcion == null"
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
                                v-model="resource.fecha_liberacion"
                                label="Fecha de inicio del beneficio"
                                placeholder="Indicar fecha"
                                show-required
                                tooltip="Fecha en la que se libera el beneficio como confirmado y se confirma a los colaboradores en lista."
                                :min="minDate(resource.fin_inscripcion)"
                                :disabled="resource.fin_inscripcion == null"
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
                                tooltip="Correo cuando se acaben la fechas de inscripciones puedan coordinar para acceder al beneficio"
                            />
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="12 mb-0">
                            <hr class="mb-3">
                            <span class="txt_conf_av">Configuración Avanzada</span>
                        </v-col>
                        <v-col cols="4">
                            <DefaultAutocomplete
                                dense
                                label="Tipo de agrupación de beneficio"
                                v-model="selectGroup"
                                :items="selects.lista_grupo"
                                item-text="name"
                                item-value="code"
                                :rules="rules.group"
                                tooltip="A qué grupo se asignará el beneficio (Generales o IR Academy)"
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
                                                                        <v-col cols="1"></v-col>
                                                                        <v-col cols="11">
                                                                            <v-row>
                                                                                <v-col cols="12">
                                                                                    <div class="lbl_silabo_bloque">Descripción</div>
                                                                                    <div class="border_editor_silabo">
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
                                                                                    </div>
                                                                                </v-col>
                                                                                <v-col cols="12">
                                                                                    <div class="lbl_silabo_bloque">Programación</div>
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
                                                                                                @click:minute="$refs.menu_time_silabo[i].save(silabo.value_time)"
                                                                                                ></v-time-picker>
                                                                                            </v-menu>
                                                                                        </div>
                                                                                    </div>
                                                                                </v-col>
                                                                            </v-row>
                                                                        </v-col>
                                                                    </v-row>
                                                                </div>
                                                            </div>
                                                        </transition-group>
                                                    </draggable>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <v-btn color="primary" outlined @click="addSilabo(null)">
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
                        <v-col cols="12" class="bx_tooltip_card">
                            <DefaultModalSection
                                title="Link o código de beneficio"
                                tooltip="El colaborador tendrá acceso a el/los link(s) cuando esté confirmado para su beneficio."
                                :right="false"
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
                                                                                placeholder="Agregar link o código de bonificación"
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
                                                <GmapAutocomplete ref="autocompleteMap" :position.sync="markers[0].position" @place_changed="setPlace" class="custom-default-input  my-2 pl-2" placeholder="Ingresa la dirección donde se realizara el curso"/>
                                            </div>
                                        </v-col>
                                        <v-col cols="4" class="d-flex justify-content-center align-items-center bx_benefit_accesible">
                                            <DefaultToggle
                                                v-model="resource.accesible"
                                                active-label="Accesible para usuarios con discapacidad"
                                                inactive-label="Accesible para usuarios con discapacidad"
                                            />
                                        </v-col>
                                    </v-row>
                                    <div class="row">
                                        <v-col cols="12">
                                            <div class="bx_maps_benefit" id="bx_maps_benefit" ref="bx_maps_benefit">
                                                <GmapMap
                                                    :center="center"
                                                    :zoom="zoom"
                                                    :options="{
                                                        zoomControl: false,
                                                        mapTypeControl: false,
                                                        scaleControl: false,
                                                        streetViewControl: false,
                                                        rotateControl: false,
                                                        fullscreenControl: false,
                                                        disableDefaultUi: false
                                                        }"
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
                                                placeholder="Ingresa una referencia de como llegar al lugar donde se realizará el curso"
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
                    <v-row>
                        <!-- Duración -->
                        <v-col cols="6" v-if="options_modules[7].active">
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
                        <!-- End Duración -->
                        <!-- Speaker -->
                        <v-col cols="6" v-if="options_modules[4].active">
                            <DefaultModalSection
                                title="Selecciona tu facilitador(a)"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_speaker d-flex">
                                        <div class="box_input_speaker">
                                            <div v-if="!resource.speaker" class="d-flex align-center">
                                                <div class="bx_speaker_img"></div>
                                                <div class="bx_speaker_name">
                                                    <span>Selecciona un facilitador(a)</span>
                                                </div>
                                            </div>
                                            <div v-if="resource.speaker" class="d-flex align-center">
                                                <div class="bx_speaker_img">
                                                    <img :src="resource.speaker.image">
                                                </div>
                                                <div class="bx_speaker_name">
                                                    <span>{{resource.speaker.name}}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box_button_speaker">
                                            <v-btn color="primary" outlined @click="openModalSelectSpeaker">
                                                Seleccionar facilitador(a)
                                            </v-btn>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                        <!-- End Speaker -->
                        <!-- Tags -->
                        <v-col cols="6" v-if="options_modules[5].active">
                            <DefaultModalSection
                                title="Selecciona un tag"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_tags d-flex justify-content-center">
                                        <div class="box_input_etiqueta">
                                            <DefaultAutocomplete
                                                dense
                                                label="Tag"
                                                placeholder="Selecciona un tag"
                                                v-model="resource.dificultad"
                                                :items="selects.lista_etiquetas"
                                                item-text="name"
                                                item-value="code"
                                            />
                                        </div>
                                        <!-- <div class="box_button_etiqueta">
                                            <v-btn color="primary" outlined @click="addLinkExterno">
                                                Agregar etiqueta
                                            </v-btn>
                                        </div> -->
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                        <!-- End Tags -->
                        <!-- Promotor -->
                        <v-col cols="6" v-if="options_modules[0].active">
                            <DefaultModalSection
                                title="Promotor"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_promotor d-flex">
                                        <div class="box_input_promotor">
                                            <DefaultInput
                                                dense
                                                label="Promotor"
                                                placeholder="Indicar la empresa que promociona"
                                                v-model="resource.promotor"
                                                :rules="rules.promotor"
                                            />
                                        </div>
                                        <div class="box_button_promotor">
                                            <v-btn color="primary" outlined @click="openModalSelectLogoPromotor">
                                                <span v-if="image_promotor_selected">
                                                    Logotipo agregado
                                                </span>
                                                <span v-else>
                                                    Agregar Logotipo
                                                </span>
                                            </v-btn>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                        <!-- End Promotor -->
                    </v-row>
                    <!-- Encuesta -->
                    <v-row>
                        <v-col cols="12" v-if="options_modules[8].active">
                            <DefaultModalSection
                                title="Encuesta"
                            >
                                <template slot="content">
                                    <v-row>
                                        <v-col cols="8">
                                            <div class="box_beneficio_encuesta d-flex justify-content-center">
                                                <div class="box_input_encuesta">
                                                    <DefaultAutocomplete
                                                        :rules="rules.lista_encuestas"
                                                        dense
                                                        :clearable="true"
                                                        label="Encuesta"
                                                        placeholder="Agrega una encuesta"
                                                        v-model="resource.poll_id"
                                                        :items="selects.lista_encuestas"
                                                        item-text="name"
                                                        item-value="id"
                                                    />
                                                </div>
                                                <div class="box_button_encuesta">
                                                    <!--
                                                    <v-btn color="primary" outlined @click="addLinkExterno">
                                                        <div class="img mr-1"><img src="/img/benefits/icono_link.svg"></div>
                                                        Link externo
                                                    </v-btn>
                                                    -->
                                                </div>
                                            </div>
                                        </v-col>
                                        <v-col cols="4">
                                            <div class="box_input_encuesta">
                                                <DefaultInputDate
                                                    clearable
                                                    dense
                                                    :top="true"
                                                    :referenceComponent="'modalDateEncuesta'"
                                                    :options="modalDateEncuesta"
                                                    v-model="resource.fecha_encuesta"
                                                    label="Fecha de liberación de encuesta"
                                                    placeholder="Agregar una fecha"
                                                />
                                            </div>
                                        </v-col>
                                    </v-row>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Encuesta -->
                    <!-- Implementos -->
                    <v-row>
                        <v-col cols="12" v-if="options_modules[6].active">
                            <DefaultModalSection
                                title="Implementos"
                            >
                                <template slot="content">
                                    <div class="box_beneficio_implementos d-flex">
                                        <span class="lbl_ben_implementos">Implementos necesarios</span>
                                        <div class="box_input_implementos">
                                            <div class="box_list_implementos">
                                                <span class="item_implementos"
                                                      v-for="(implemento, i) in lista_implementos"
                                                      :key="implemento.id">
                                                    {{implemento.name}}
                                                    <v-icon @click="lista_implementos.splice(i, 1);">
                                                        mdi-close-circle
                                                    </v-icon>
                                                </span>
                                            </div>
                                            <div class="box_text_implementos" v-if="show_text_add_implement">
                                                <input type="text" v-model="text_add_implement" v-on:keyup.enter="actionAddImplement" ref="text_add_implement" id="text_add_implement"/>
                                            </div>
                                            <div class="box_button_implementos">
                                                <v-btn color="primary" @click="actionButtonAddImplement">
                                                    Agregar
                                                </v-btn>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSection>
                        </v-col>
                    </v-row>
                    <!-- End Implementos -->

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
            @newSpeaker="newSpeaker"
            />
        <ModalSelectLogoPromotor
            :ref="modalLogoPromotor.ref"
            v-model="modalLogoPromotor.open"
            :data="modalLogoPromotor.data"
            width="650px"
            @closeModalSelectLogoPromotor="modalLogoPromotor.open = false"
            @confirmSelectLogoPromotor="confirmSelectLogoPromotor"
            @confirmSelectLogoPromotorOrdenador="confirmSelectLogoPromotorOrdenador"
            />
    </section>
</template>
<script>
const fields = [
    'title',
    'active',
    'position',
    'image',
    'type_id',
    'description',
    'correo',
    'cupos',
    'description',
    'dificultad',
    'accesible',
    'duracion',
    'fin_inscripcion',
    'inicio_inscripcion',
    'fecha_liberacion',
    'list_links',
    'lista_encuestas',
    'promotor',
    'promotor_imagen_multimedia',
    'referencia',
    // 'speaker',
    'type',
    // 'ubicacion_mapa',
    'list_silabos',
    'lista_grupo',
    'group',
    'fecha_encuesta',
    'poll_id',
    'promotor_imagen'
];
const file_fields = ['image','promotor_imagen'];

import DialogConfirm from "../../components/basicos/DialogConfirm";
import Editor from "@tinymce/tinymce-vue";
import GmapMap from 'vue2-google-maps/dist/components/map.vue'
import html2canvas from 'html2canvas';
import ModalAddLink from "../../components/Benefit/ModalAddLink";
import ModalSelectSpeaker from "../../components/Benefit/ModalSelectSpeaker";
import ModalSelectLogoPromotor from "../../components/Benefit/ModalSelectLogoPromotor";

export default {
    components: {DialogConfirm, Editor,GmapMap, ModalAddLink, ModalSelectSpeaker, ModalSelectLogoPromotor},
    props: [ 'benefit_id', 'api_key_maps'],
    data() {
        return {
            // para implementos
            text_add_implement: null,
            show_text_add_implement: false,
            // para el mapa
            center: { lat: -12.0529046, lng: -77.0253457 },
            zoom: 16,
            currentPlace: null,
            markers: [{
                position: { lat: -12.0529046, lng: -77.0253457 }
            }],
            ubicacion_mapa: null,
            // para el desplegable
            menu: false,
            options_modules: [
                {name: 'Promotor del beneficio', code: 'promotor', active: false},
                {name: 'Sílabo', code: 'silabo', active: false},
                {name: 'Ubicación / Mapa', code: 'ubicacion', active: false},
                {name: 'Agregar link', code: 'links', active: false},
                {name: 'Facilitadores', code: 'speaker', active: false},
                {name: 'Tags', code: 'dificultad', active: false},
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
            // modal logo promotor

            modalLogoPromotor: {
                ref: 'modalLogoPromotor',
                open: false,
                data: [],
                endpoint: '',
            },

            // otros
            image_promotor_selected: false,
            promotor_imagen: null,
            promotor_imagen_ordenador: null,
            drag_links: false,
            drag_silabos: false,
            list_links: [],
            list_silabos: [],
            lista_etiquetas: [],
            lista_implementos: [],
            lista_grupo: [],
            selectType: null,
            selectGroup: null,
            activeDificultad: null,
            duracionValue: null,
            duracionIlimitado: null,
            cupoValue: null,
            cupoIlimitado: null,
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
            },
            modalDateFilter3: {
                open: false,
            },
            modalDateEncuesta: {
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
                promotor_imagen_multimedia: null,
                // position: null,
                image: null,
                file_image: null,
                promotor_imagen: null,
                file_promotor_imagen: null,
                active: true,
                type_id: null,
                inicio_inscripcion: null,
                fin_inscripcion: null,
                fecha_liberacion: null,
                fecha_encuesta: null,
                correo: null,
                poll_id: null,
                list_types: [],
                lista_encuestas: [],
                list_silabos: [],
                lista_etiquetas: [],
                lista_implementos: [],
                lista_grupo: [],
                dificultad: null,
            },
            resource: {},
            rules: {
                title: this.getRules(['required', 'max:200']),
                list_types: this.getRules(['required']),
                correo: this.getRules(['required']),
                group: this.getRules(['required']),
                inicio_inscripcion: this.getRules(['required']),
                fin_inscripcion: this.getRules(['required']),
                fecha_liberacion: this.getRules(['required']),
            },
            selects: {
                lista_encuestas: [],
                list_types: [],
                lista_etiquetas: [],
                lista_grupo: []
            },
            loadingActionBtn: false
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
        minDate( date = null ){
            if(date != null)
            {
                let result = new Date(date);
                result.setDate(result.getDate() + 1);
                return result.toISOString().substr(0, 10);
            }
            return new Date().toISOString().substr(0, 10)
        },
        actionButtonAddImplement() {
            let vue = this
            if(vue.show_text_add_implement) {
                vue.actionAddImplement()
            }
            else {
                vue.show_text_add_implement = true
                setTimeout(function () {
                    vue.$refs.text_add_implement.focus()
                }, 1)
            }
        },
        actionAddImplement() {
            let vue = this
            if(vue.text_add_implement != null && vue.text_add_implement != '') {
                const newID = `n-${Date.now()}`;
                let newImplement = {
                    id: newID,
                    name: vue.text_add_implement,
                    active: 1,
                    benefit_id: vue.resource.id,
                }
                vue.lista_implementos.push(newImplement)
                vue.text_add_implement = null
                vue.show_text_add_implement = false
            }
        },
        addItemImplementos() {

        },
         openModalSelectLogoPromotor() {
            let vue = this;
            vue.modalLogoPromotor.open = true
        },
        confirmSelectLogoPromotor( value ){
            let vue = this;
            vue.modalLogoPromotor.open = false

            vue.image_promotor_selected = true
            vue.promotor_imagen = null
            if(value){
                vue.image_promotor_selected = true
                vue.promotor_imagen = value
            }
        },
        confirmSelectLogoPromotorOrdenador( value ){
            let vue = this;
            vue.modalLogoPromotor.open = false

            vue.image_promotor_selected = true
            vue.promotor_imagen_ordenador = null
            if(value){
                vue.image_promotor_selected = true
                vue.promotor_imagen_ordenador = value
            }
        },
        async openModalSelectSpeaker() {
            let vue = this;

            vue.showLoader();

            vue.modalSelectSpeaker.open = true

                await vue.$http.get(`/beneficios/speakers/search`)
                    .then((res) => {
                        let res_speakers = res.data.data.data;
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
            vue.resource.speaker = value
            vue.modalSelectSpeaker.open = false
        },
        confirmAddLink( value ) {
            let vue = this;
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
            vue.list_links.push(newLink);
        },
        eliminarLink(link, index) {
            let vue = this;
            vue.list_links.splice(index, 1);
        },
        addSilabo( data = null) {
            let vue = this;
            const newID = `n-${Date.now()}`;
            let newSilabo = {};
            if( data != null) {
                newSilabo = {
                    id: data.id,
                    ref: newID,
                    name: data.name,
                    value: data.value,
                    value_date: data.value_date,
                    value_time: data.value_time,
                    menu_value_time: false,
                    active: data.active,
                    benefit_id: data.benefit_id,
                    hasErrors: false,
                    is_default:false,
                    expanded: false,
                    modalDateSilabo: {
                        open: false,
                    },
                };
            }
            else {
                newSilabo = {
                    id: newID,
                    ref: newID,
                    name: "",
                    value: "",
                    value_date:  null,
                    value_time: null,
                    menu_value_time: false,
                    active: 1,
                    benefit_id: vue.resource.id,
                    hasErrors: false,
                    is_default:false,
                    expanded: false,
                    modalDateSilabo: {
                        open: false,
                    },
                };
            }
            vue.list_silabos.push(newSilabo);
        },
        eliminarSilabo(silabo, index) {
            let vue = this;
            vue.list_silabos.splice(index, 1);
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
            if (this.currentPlace) {
                this.ubicacion_mapa = {...this.currentPlace}
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
                    this.ubicacion_mapa = {...result[0]}
                }
            })
        },
        images_upload_handler(blobInfo, success, failure) {
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
        dataURLtoBlob(dataURL) {
            // Decode the dataURL
            var binary = atob(dataURL.split(',')[1]);
            // Create 8-bit unsigned array
            var array = [];
            for(var i = 0; i < binary.length; i++) {
                array.push(binary.charCodeAt(i));
            }
            // Return our Blob object
            return new Blob([new Uint8Array(array)], {type: 'image/png'});
        },
        async confirmModal(validateForm = true) {
            let vue = this
            vue.errors = []

            if( vue.duracionIlimitado == 'ilimitado' ) {
                vue.resource.duracion = 'ilimitado'
            }
            else if( vue.duracionValue != null && vue.duracionValue != '' ) {
                vue.resource.duracion = vue.duracionValue
            }

            if( vue.cupoIlimitado == 'ilimitado' ) {
                vue.resource.cupos = 'ilimitado'
            }
            else if( vue.cupoValue != null && vue.cupoValue != '' ) {
                vue.resource.cupos = vue.cupoValue
            }

            if( vue.promotor_imagen != null ) {
                vue.resource.promotor_imagen_multimedia = vue.promotor_imagen
            }

            if( vue.promotor_imagen_ordenador != null ) {
                vue.resource.file_promotor_imagen = vue.promotor_imagen_ordenador
            }

            vue.resource.type = vue.selectType
            vue.resource.group = vue.selectGroup

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

            // Clear values from inactive configurations

            if (!vue.isModuleOptionActive('promotor'))
                vue.resource.promotor = null;

            if (!vue.isModuleOptionActive('speaker'))
                vue.resource.speaker = null;

            if (!vue.isModuleOptionActive('dificultad'))
                vue.resource.dificultad = null;

            if (!vue.isModuleOptionActive('duracion'))
                vue.resource.duracion = null;

            if (!vue.isModuleOptionActive('silabo'))
                vue.list_silabos = [];

            if (!vue.isModuleOptionActive('links'))
                vue.list_links = [];

            if (!vue.isModuleOptionActive('implementos'))
                vue.lista_implementos = [];

            if (!vue.isModuleOptionActive('encuesta')) {
                vue.resource.poll_id = null;
                vue.resource.fecha_encuesta = null;
            }

            // Preperate data

            const formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
            formData.append('validateForm', validateForm ? "1" : "0");

            let list_silabos = JSON.stringify(vue.list_silabos)
            formData.append('list_silabos', list_silabos)

            let list_links = JSON.stringify(vue.list_links)
            formData.append('list_links', list_links)

            let lista_implementos = JSON.stringify(vue.lista_implementos)
            formData.append('lista_implementos', lista_implementos)

            let speaker = JSON.stringify(vue.resource.speaker)
            formData.append('speaker', speaker)

            if( vue.ubicacion_mapa != null )
            {

                let file_image_maps = null;
                let bx_canvas = vue.$refs.bx_maps_benefit;

                html2canvas(bx_canvas, {
                    width: bx_canvas.offsetWidth,
                    height: bx_canvas.offsetHeight,
                    allowTaint : true,
                    logging: true,
                    profile: true,
                    useCORS: true,
                }).then(function(canvas) {

                    file_image_maps = canvas.toDataURL('image/png');
                    file_image_maps = file_image_maps != null ? vue.dataURLtoBlob(file_image_maps) : null;

                    let data_maps = {
                        image_map: null,
                        geometry: null,
                        formatted_address: null,
                        url: null,
                        ubicacion: null,
                    }

                    data_maps.geometry = vue.ubicacion_mapa.geometry
                    data_maps.formatted_address = vue.ubicacion_mapa.formatted_address
                    data_maps.url = vue.ubicacion_mapa.url

                    for (let j = 0; j < vue.ubicacion_mapa.address_components.length; j++) {
                        if (vue.ubicacion_mapa.address_components[j].types[0] == "locality") {
                            data_maps.ubicacion = vue.ubicacion_mapa.address_components[j].long_name;
                            break;
                        }
                    }

                    let formdata2 = new FormData();
                    formdata2.append('image', file_image_maps, 'maps_'+ vue.resource.title.replace(/\s/g, '_'))
                    formdata2.append("model_id", null);

                    vue.$http
                        .post("/upload-image/beneficios", formdata2)
                        .then(async (res) => {
                            data_maps.image_map = res.data.location

                            let ubicacion_mapa = JSON.stringify(data_maps)
                            formData.append('ubicacion_mapa', ubicacion_mapa)

                            await vue.$http.post(url, formData)
                                    .then(async ({data}) => {
                                        vue.hideLoader()
                                        vue.showAlert(data.data.msg)
                                        setTimeout(() => vue.closeModal(), 2000)
                                    })
                                    .catch(error => {
                                        if (error && error.errors){
                                            vue.errors = error.errors
                                        }
                                        vue.loadingActionBtn = false
                                    })
                        })
                        .catch((err) => {
                            console.log("upload failed!");
                        });
                });
            }
            else
            {
                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            setTimeout(() => vue.closeModal(), 2000)
                        })
                        .catch(error => {
                            if (error && error.errors){
                                vue.errors = error.errors
                            }
                            vue.loadingActionBtn = false
                        })
            }


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
                    vue.selects.lista_grupo = response.group
                    vue.selects.lista_etiquetas = response.tags
                    if(vue.benefit_id == '') {
                        vue.selectGroup = 'free';
                    }
                })
            if(vue.benefit_id != '') {
                let url = `${vue.base_endpoint}/search/${vue.benefit_id}`
                await vue.$http.get(url)
                    .then(({data}) => {
                        let response = data.data.data;

                        this.selectType = (response.type != null) ? response.type.code : null
                        this.selectGroup = (response.group != null) ? response.group.code : null

                        if(response.promotor_imagen != null) {
                            vue.image_promotor_selected = true
                            // vue.promotor_imagen = response.promotor_imagen
                        }

                        if(response.direccion != null && response.direccion.address != null) {
                            setTimeout(() => {
                                vue.$refs.autocompleteMap.$refs.input.value = response.direccion.address
                            }, 2000);
                        }

                        if(response.cupos == null)
                            vue.cupoIlimitado = 'ilimitado'
                        else
                            vue.cupoValue = response.cupos

                        if(response.silabo != null && response.silabo.length > 0) {
                            vue.options_modules[1].active = true
                            response.silabo.forEach(element => {
                                vue.addSilabo(element)
                            });
                        }
                        if(response.links != null && response.links.length > 0) {
                            vue.options_modules[3].active = true
                            response.links.forEach(element => {
                                const newLink = {
                                    id: element.id,
                                    name: element.name,
                                    value: element.value,
                                    active: element.active,
                                    benefit_id: element.benefit_id,
                                    hasErrors: false,
                                    is_default:false
                                };
                                vue.list_links.push(newLink);
                            });
                        }
                        if(response.speaker != null) {
                            vue.options_modules[4].active = true
                        }

                        if((response.promotor != null && response.promotor != '') ||
                        (response.promotor_imagen != null && response.promotor_imagen != '')) {
                            vue.options_modules[0].active = true
                        }

                        if(response.dificultad != null && response.dificultad != '') {
                            vue.options_modules[5].active = true
                        }

                        if((response.referencia != null && response.referencia != '') ||
                            (response.direccion != null && response.direccion != '')) {
                            vue.options_modules[2].active = true
                        }

                        if(response.duracion != null && response.duracion != '') {
                            vue.options_modules[7].active = true
                            if(response.duracion == 'ilimitado')
                                vue.duracionIlimitado = response.duracion
                            else
                                vue.duracionValue = response.duracion
                        }

                        if(response.implements != null && response.implements.length > 0) {
                            vue.options_modules[6].active = true
                            response.implements.forEach(element => {
                                let newImplement = {
                                    id: element.id,
                                    name: element.name,
                                    active: element.active,
                                    benefit_id: element.benefit_id,
                                }
                                vue.lista_implementos.push(newImplement)
                            });
                        }

                        if (response.poll_id) {
                            vue.options_modules[8].active = true
                        }

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
            if (this.selectGroup == null || this.selectGroup == '') {
                errors.push({
                    message: 'Debe seleccionar un grupo de beneficio'
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
        },
        newSpeaker(){
            let vue = this;
            vue.modalSelectSpeaker.open = false
            window.open(`/speakers/create?mode=assign`)
        },
        isModuleOptionActive(code) {

            let option = this.options_modules.find(o => o.code === code)
            if (option) {
                return option.active;
            } else {
                return false;
            }
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
.box_items_dificultad .item_dificultad {
    border: 1px solid #D9D9D9;
    margin: 0 10px;
    padding: 10px 20px;
    min-width: 150px;
    text-align: center;
    color: #D9D9D9;
    font-weight: 600;
    font-family: 'open sans', "Nunito", sans-serif;
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
.box_input_etiqueta,
.box_input_encuesta {
    width: 100%;
    margin-right: 10px;
}
.box_button_encuesta button .img img,
.box_button_etiqueta button .img img {
    max-width: 20px;
}
.box_button_duracion .v-input__control .v-messages {
    display: none;
}
.box_button_duracion .v-input__control .v-input__slot,
.box_button_duracion .v-input__control .v-input__slot .v-radio .v-label {
    margin-bottom: 0;
    font-family: 'open sans', "Nunito", sans-serif;
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
        overflow: hidden;
        align-items: center;
        justify-content: center;
        img {
            max-width: 100%;
        }
    }
}
.box_button_speaker button {
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    height: 44px !important;
}
.box_beneficio_implementos {
    position: relative;

    .lbl_ben_implementos {
        position: absolute;
        top: -10px;
        left: 10px;
        background: #fff;
        font-size: 12px;
        color: rgba(0,0,0,.6);
    }
    .box_input_implementos {
        width: 100%;
        border: 1px solid #D9D9D9;
        border-radius: 4px;
        padding: 16px 10px 10px;

        .box_list_implementos,
        .box_text_implementos,
        .box_button_implementos {
            display: inline-flex;
        }
        .box_list_implementos {
            flex-wrap: wrap;
        }
        .box_text_implementos input {
            padding: 3px 14px;
            border-radius: 30px;
            border: 1px solid #5457E7;
            margin-right: 5px;
            font-family: 'open sans', "Nunito", sans-serif;
            font-size: 14px;
            color: #5457E7;
            position: relative;
        }
        .item_implementos {
            padding: 3px 14px;
            border-radius: 30px;
            border: 1px solid #5457E7;
            margin-right: 5px;
            font-family: 'open sans', "Nunito", sans-serif;
            font-size: 14px;
            color: #5457E7;
            padding-right: 28px;
            position: relative;
            margin-bottom: 5px;

            button.v-icon {
                color: #5458ea;
                font-size: 15px;
                position: absolute;
                top: 50%;
                right: 8px;
                transform: translateY(-50%);
                cursor: pointer;
            }
        }
        .box_button_implementos button {
            border-radius: 30px;
        }
    }
}
.border_editor_silabo {
    border: 1px solid #d9d9d9;
    border-radius: 4px;
}
.lbl_silabo_bloque {
    font-family: 'open sans', "Nunito", sans-serif;
    color: #666666;
    font-size: 13px;
}
.txt_conf_av {
    font-size: 15px;
    color: #5458EA;
    font-family: 'open sans', "Nunito", sans-serif;
    font-weight: 700;
}
.bx_tooltip_card .v-tooltip__content {
    background-color: #fff;
    color: #5757EA;
    border: 1px solid #5757EA;
    border-radius: 10px;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    width: 274px;
}
.bx_tooltip_card .v-tooltip--bottom .v-tooltip__content {
    top: 35px !important;
}
</style>
