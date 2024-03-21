

<template>
    <v-dialog :max-width="width" v-model="value" scrollable
        :persistent="true"
        @click:outside="closeModalOutside">
        <v-card class="modal_edit_process">
            <v-card-title class="default-dialog-title">
                {{ process.id ? 'Editar' : 'Crear' }} proceso de inducción {{ step_title }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0">

                <v-stepper non-linear class="stepper_box" v-model="stepper_box">
                    <v-stepper-items>
                        <v-stepper-content step="1" class="p-0">
                            <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0 mb-3">
                                            <span class="text_default lbl_tit">Indica la información que tendrá este proceso de inducción</span>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="7" class="pb-0">
                                            <DefaultInput
                                                        v-model="process.title"
                                                        label="Ingresa el nombre del proceso"
                                                        :rules="rules.title"
                                                        show-required
                                            />
                                        </v-col>
                                        <v-col cols="5">
                                            <DefaultAutocomplete
                                                show-required
                                                :rules="rules.subworkspaces"
                                                dense
                                                label="Módulos"
                                                v-model="process.subworkspaces"
                                                :items="selects.subworkspaces"
                                                item-text="name"
                                                item-value="id"
                                                multiple
                                                :count-show-values="1"
                                            />
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0">
                                            <div>
                                                <fieldset class="editor">
                                                    <legend>Ingresa aquí el texto de bienvenida al momento de ingresar en la inducción *
                                                    </legend>
                                                    <editor
                                                        @input="maxCharacters(process.description)"
                                                        api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                                        v-model="process.description"
                                                        :init="{
                                                            content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                                            height: 170,
                                                            menubar: false,
                                                            language: 'es',
                                                            force_br_newlines : true,
                                                            force_p_newlines : false,
                                                            forced_root_block : '',
                                                            plugins: ['lists image preview anchor', 'code', 'paste','link','emoticons'],
                                                            toolbar:
                                                                'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                                            images_upload_handler: images_upload_handler,
                                                            toolbar_location: 'bottom'
                                                        }"
                                                    />
                                                </fieldset>
                                                <span class="text_default txt_counter d-flex justify-content-end">{{ process.description.length }}/350</span>
                                            </div>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12">
                                            <div class="row_dates d-flex">
                                                <div class="d-flex align-center">
                                                    <span class="text_default mr-2">Fecha de inicio del proceso:</span>
                                                    <div class="bx_input_date">
                                                        <DefaultInputDate
                                                            placeholder="Ingresar fecha *"
                                                            reference-component="StartProcessDate"
                                                            :options="modalDateOptions"
                                                            label=""
                                                            v-model="process.starts_at"
                                                            :offset-y="false"
                                                            :offset-x="true"
                                                            :top="true"
                                                        />
                                                    </div>
                                                </div>
                                                <div class="d-flex align-center">
                                                    <span class="text_default mr-2">Fecha de fin del proceso: (opcional)</span>
                                                    <div class="bx_input_date">
                                                        <DefaultInputDate
                                                            placeholder="Ingresar fecha"
                                                            reference-component="StartProcessDate"
                                                            :options="modalDateOptions2"
                                                            label=""
                                                            v-model="process.finishes_at"
                                                            :offset-y="false"
                                                            :offset-x="true"
                                                            :top="true"
                                                            :left="true"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12" class="d-flex align-items-center">
                                            <div class="row_border">
                                                <div class="d-flex align-center">
                                                    <span class="text_default me-2">Inasistencias</span>
                                                    <div class="bx_switch_attendance">
                                                        <v-switch
                                                            class="default-toggle"
                                                            inset
                                                            hide-details="auto"
                                                            v-model="process.count_absences"
                                                            dense
                                                        ></v-switch>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-center">
                                                    <span class="text_default lbl_tit mr-2" :class="{'disabled': !process.count_absences}">¿Deseas agregar límite de inasistencia a este proceso de inducción?</span>
                                                    <div class="d-flex">
                                                        <div class="bx_input_inasistencias" :class="{'disabled': !process.count_absences}">
                                                            <input type="number" v-model="process.absences" :readonly="!process.count_absences">
                                                        </div>
                                                        <div class="ml-4">
                                                            <!-- <v-radio-group v-model="process.limit_absences" row>
                                                                <v-radio label="No" :value="false"></v-radio>
                                                                <div class="divider_inline"></div>
                                                                <v-radio label="Sí" :value="true"></v-radio>
                                                            </v-radio-group> -->
                                                            <v-checkbox
                                                                v-model="process.limit_absences"
                                                                hide-details="false"
                                                                class="my-0 mr-4"
                                                                :class="{'disabled_label': !process.count_absences}"
                                                                label="Ilimitado"
                                                                :disabled="!process.count_absences"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="2" class="p-0">
                            <v-card style="box-shadow:none !important;" class="bx_steps bx_step3">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                            <span class="text_default lbl_tit">Indica la información que tendrá el onboarding</span>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="5">
                                            <v-row>
                                                <v-col cols="12" class="pb-1">
                                                    <span class="text_default">Previsualización móvil</span>
                                                </v-col>
                                                <v-col cols="12" class="pt-1">
                                                    <div class="bx_preview_colors">
                                                        <div class="bx_preview_map">
                                                            <PreviewMap :color="colorSelected" :fondoMapa="colorMapaSelected"/>
                                                        </div>
                                                        <div class="bg_instructions_profile">
                                                            <div class="bg_bubble" :style="backgroundColorSelected"></div>
                                                            <div class="bx_change_img_profile">
                                                                <div class="bx_img_profile">
                                                                    <img :src="process.image_guia ? process.image_guia : '/img/induccion/personalizacion/perfil-hombre.png'">
                                                                    <div class="icon_edit" @click="openFormModal(modalAvatarsRepository, process)" ><img src="/img/induccion/personalizacion/edit_color.svg"></div>
                                                                </div>
                                                                <span class="text_default">Cambiar guía</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                        <v-col cols="7">
                                            <v-row>
                                                <v-col cols="12">
                                                    <div class="d-flex">
                                                        <div style="border-right: 1px solid #A9B2B9; margin-right: 15px;">
                                                            <div>
                                                                <span class="text_default">Color tema</span>
                                                            </div>
                                                            <div>
                                                                <div class="box_select_colors">
                                                                    <div class="item_color_onb" @click="colorSelected = colorDefault" :class="[colorSelected == colorDefault ? 'selected' : '']">
                                                                        <span class="text_default">Cursalab</span>
                                                                        <div class="bg_color_item bg_color_item_default">
                                                                            <div class="color_d"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="item_color_onb" :class="[colorSelected != colorDefault ? 'selected' : '']">
                                                                        <span class="text_default">Editable</span>
                                                                        <div class="bg_color_item">
                                                                            <v-btn  hide-details class="ma-0 pa-0" solo @click="changeColorSelected" :style="swatchStyle">
                                                                                <div class="icon_edit"><img src="/img/induccion/personalizacion/edit_color.svg"></div>
                                                                            </v-btn>
                                                                            <v-menu v-model="menuPicker"
                                                                                    bottom
                                                                                    :close-on-content-click="false"
                                                                                    offset-y
                                                                                    right
                                                                                    nudge-bottom="10"
                                                                                    min-width="auto">
                                                                                <template v-slot:activator="{ on }">
                                                                                    <div  v-on="on" />
                                                                                </template>
                                                                                <v-card>
                                                                                    <v-card-text class="pa-0">
                                                                                        <v-color-picker v-model="colorPicker" mode="hexa" flat />
                                                                                    </v-card-text>
                                                                                </v-card>
                                                                            </v-menu>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <div>
                                                                <span class="text_default">Fondo de mapa</span>
                                                            </div>
                                                            <div>
                                                                <div class="box_select_colors colors_maps">
                                                                    <div class="item_color_onb">
                                                                        <span class="text_default">Mapa 1</span>
                                                                        <div class="bg_color_item">
                                                                            <v-btn  hide-details class="ma-0 pa-0" solo @click="changeColorImparSelected" :style="swatchStyleImpar">
                                                                                <div class="icon_edit"><img src="/img/induccion/personalizacion/edit_color.svg"></div>
                                                                            </v-btn>
                                                                            <v-menu v-model="menuImparPicker"
                                                                                    bottom
                                                                                    :close-on-content-click="false"
                                                                                    offset-y
                                                                                    right
                                                                                    nudge-bottom="10"
                                                                                    min-width="auto">
                                                                                <template v-slot:activator="{ on }">
                                                                                    <div  v-on="on" />
                                                                                </template>
                                                                                <v-card>
                                                                                    <v-card-text class="pa-0">
                                                                                        <v-color-picker v-model="colorImparPicker" mode="hexa" flat />
                                                                                    </v-card-text>
                                                                                </v-card>
                                                                            </v-menu>
                                                                        </div>
                                                                    </div>
                                                                    <div class="item_color_onb">
                                                                        <span class="text_default">Mapa 2</span>
                                                                        <div class="bg_color_item">
                                                                            <v-btn  hide-details class="ma-0 pa-0" solo @click="changeColorParSelected" :style="swatchStylePar">
                                                                                <div class="icon_edit"><img src="/img/induccion/personalizacion/edit_color.svg"></div>
                                                                            </v-btn>
                                                                            <v-menu v-model="menuParPicker"
                                                                                    bottom
                                                                                    :close-on-content-click="false"
                                                                                    offset-y
                                                                                    right
                                                                                    nudge-bottom="10"
                                                                                    min-width="auto">
                                                                                <template v-slot:activator="{ on }">
                                                                                    <div  v-on="on" />
                                                                                </template>
                                                                                <v-card>
                                                                                    <v-card-text class="pa-0">
                                                                                        <v-color-picker v-model="colorParPicker" mode="hexa" flat />
                                                                                    </v-card-text>
                                                                                </v-card>
                                                                            </v-menu>
                                                                        </div>
                                                                    </div>
                                                                    <div class="pt-3">
                                                                        <span class="d-flex text_default fw-bold mb-2" style="line-height: 1; font-size: 12px;">Tendrás dos estilos de mapas en tu proceso.</span>
                                                                        <span class="d-flex text_default" style="font-size: 12px;">Mapa 1: Etapas pares</span>
                                                                        <span class="d-flex text_default" style="font-size: 12px;">Mapa 2: Etapas impares</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <span class="text_default fw-bold" style="font-size: 12px;">Al tener 2 o más etapas se tendrán distintos mapas</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-4 mb-2">
                                                        <span class="text_default">Íconos al terminar el proceso</span>
                                                    </div>
                                                    <div>
                                                        <div class="box_select_icons">
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_1')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_1')">
                                                                    <img src="/img/induccion/personalizacion/apreton-de-manos.png" ref="img_onb_1">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_2')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_2')">
                                                                    <img src="/img/induccion/personalizacion/rascacielos.png" ref="img_onb_2">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_3')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_3')">
                                                                    <img src="/img/induccion/personalizacion/fabrica.png" ref="img_onb_3">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_4')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_4')">
                                                                    <img src="/img/induccion/personalizacion/producto.png" ref="img_onb_4">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_5')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_5')">
                                                                    <img src="/img/induccion/personalizacion/tienda-de-comestibles.png" ref="img_onb_5">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_6')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_6')">
                                                                    <img src="/img/induccion/personalizacion/mercado.png" ref="img_onb_6">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_7')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_7')">
                                                                    <img src="/img/induccion/personalizacion/fabrica-2.png" ref="img_onb_7">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding('img_onb_8')]" :style="backgroundColorSelected" @click="selectIconOnboarding('img_onb_8')">
                                                                    <img src="/img/induccion/personalizacion/detallista.png" ref="img_onb_8">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb" v-for="(icon, index) in list_icons_finished_onboarding" :key="index">
                                                                <div class="bg_icon_item" :class="[classSelectIconOnboarding(icon.title ? icon.title : 'name_icon_'+index)]" :style="backgroundColorSelected" @click="selectIconOnboardingUpload(icon.url ? icon.url : icon.logo_cropped, icon.title ? icon.title : 'name_icon_'+index)">
                                                                    <img :src="icon.url ? icon.url : icon.logo_cropped">
                                                                </div>
                                                            </div>
                                                            <div class="item_icono_onb">
                                                                <div class="bg_icon_item" style="background: none !important;" @click="openFormModal(modalUploadImageResize)">
                                                                    <v-icon style="color: #5458EA;">
                                                                        mdi-plus-circle
                                                                    </v-icon>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="3" class="p-0">
                            <v-card style="box-shadow:none !important;" class="bx_steps bx_step3">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                            <span class="text_default lbl_tit">Previsualización de logotipo e imágenes de fondo</span>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="5">
                                            <v-row>
                                                <v-col cols="12" class="bx_preview_change_logo">
                                                    <DefaultSelectOrUploadMultimedia
                                                        ref="inputLogo"
                                                        v-model="resource.logotipo"
                                                        label="Logotipo (500 x 500px)"
                                                        :file-types="['image']"
                                                        @onSelect="setFile($event, resource,'logotipo')"
                                                        @onPreview="logo_selected = $event"
                                                        @croppedImage="logo_cropped = $event"
                                                        @removeImage="logo_cropped = $event"
                                                        :sizeCropp="{width:500, height:500}"
                                                        :showButton="false"
                                                        :cropImage="true"
                                                        />
                                                </v-col>
                                                <v-col cols="12">
                                                    <div  v-show="tab_preview_images == 'mobile'">
                                                        <DefaultSelectOrUploadMultimedia
                                                            ref="inputFondoMobile"
                                                            v-model="process.fondo_mobile"
                                                            label="Fondo mobile (720 x 1280px)"
                                                            :file-types="['image']"
                                                            @onSelect="setFile($event, process, 'fondo_mobile')"
                                                            @onPreview="fondo_mobile_selected = $event"
                                                            @croppedImage="fondo_mobile_cropped = $event"
                                                            :sizeCropp="{width:720, height:1280}"
                                                            :showButton="false"
                                                            :cropImage="true"
                                                            />
                                                    </div>
                                                    <div v-show="tab_preview_images == 'web'">
                                                        <DefaultSelectOrUploadMultimedia
                                                            ref="inputFondoWeb"
                                                            v-model="process.fondo_web"
                                                            label="Fondo web (1280 x 720px)"
                                                            :file-types="['image']"
                                                            @onSelect="setFile($event, process, 'fondo_web')"
                                                            @onPreview="fondo_web_selected = $event"
                                                            @croppedImage="fondo_web_cropped = $event"
                                                            :sizeCropp="{width:1280, height:720}"
                                                            :showButton="false"
                                                            :cropImage="true"
                                                            />
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                        <v-col cols="7">
                                            <v-row>
                                                <v-col cols="12">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="text_default">Previsualización</span>
                                                        </div>
                                                        <div class="btns_preview_type">
                                                            <DefaultButton :label="'Mobile'"
                                                                @click="tab_preview_images = 'mobile'"
                                                                :outlined="tab_preview_images != 'mobile'"
                                                                />
                                                            <DefaultButton :label="'Web'"
                                                                @click="tab_preview_images = 'web'"
                                                                :outlined="tab_preview_images != 'web'"
                                                                />
                                                        </div>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                            <v-row style="height: calc(100% - 60px);">
                                                <v-col cols="12">
                                                    <div class="box_preview" v-if="tab_preview_images == 'mobile'">
                                                        <div class="tpl_preview_mobile" v-if="(logo_cropped && fondo_mobile_cropped) || (process.logo && process.background_mobile)">
                                                            <div class="bx_imgs_pm">
                                                                <div class="bx_fondo_pm" v-if="fondo_mobile_cropped || process.background_mobile">
                                                                    <img :src="fondo_mobile_cropped ? fondo_mobile_cropped: process.background_mobile">
                                                                </div>
                                                                <div class="bx_logo_pm" v-if="logo_cropped || process.logo">
                                                                    <img :src="logo_cropped ? logo_cropped : process.logo">
                                                                </div>
                                                            </div>
                                                            <span class="text_default">
                                                                Bienvenidos
                                                                <img src="/img/induccion/personalizacion/arrow_right.svg">
                                                            </span>
                                                        </div>
                                                        <span class="text_default" v-else>
                                                            Necesitas ambos archivos para poder generar una previsualización
                                                        </span>
                                                    </div>
                                                    <div class="box_preview" v-if="tab_preview_images == 'web'">
                                                        <div class="tpl_preview_web" v-if="(logo_cropped && fondo_web_cropped) || (process.logo && process.background_web)">
                                                            <div class="bx_imgs_pm">
                                                                <div class="bx_fondo_pm" v-if="fondo_web_cropped || process.background_web">
                                                                    <img :src="fondo_web_cropped ? fondo_web_cropped : process.background_web">
                                                                </div>
                                                                <div class="bx_logo_pm" v-if="logo_cropped || process.logo">
                                                                    <img :src="logo_cropped ? logo_cropped : process.logo">
                                                                </div>
                                                                <span class="text_default">
                                                                    Bienvenidos
                                                                    <img src="/img/induccion/personalizacion/arrow_right.svg">
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <span class="text_default" v-else>
                                                            Necesitas ambos archivos para poder generar una previsualización
                                                        </span>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-stepper-content>
                        <v-stepper-content step="4" class="p-0">
                            <v-card style="box-shadow:none !important;" class="bx_steps bx_step2">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0 mb-2">
                                            <span class="text_default lbl_tit fw-bold">Escribe un instructivo para tus trabajadores.</span>
                                        </v-col>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                            <span class="text_default lbl_tit">Da indicaciones a tus trabajadores para organizarlos o darles unas pautas al ingresar a la plataforma, en caso de no contar con un instructivo puedes puedes usar el  Instructivo Cursalab.</span>
                                        </v-col>
                                    </v-row>
                                    <v-row class="bx_instructions_list">
                                        <v-col cols="8">
                                            <div class="bx_overflow">
                                                <draggable v-model="process.instructions" @start="drag=true"
                                                        @end="drag=false" class="custom-draggable" ghost-class="ghost">
                                                    <transition-group type="transition" name="flip-list" tag="div">
                                                        <div v-for="(instruction, i) in process.instructions"
                                                            :key="instruction.id">
                                                            <div class="item-draggable activities">
                                                                <div class="item_instruction">
                                                                    <div class="ii1 d-flex align-center justify-content-center ">
                                                                        <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                                        </v-icon>
                                                                    </div>
                                                                    <div class="ii2">
                                                                        <fieldset class="editor">
                                                                            <legend>Escribe aquí una indicación
                                                                            </legend>
                                                                            <editor
                                                                                @onfocus="instructionSelected(instruction.description)"
                                                                                @input="instructionSelected(instruction.description)"
                                                                                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                                                                                v-model="instruction.description"
                                                                                :init="{
                                                                                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                                                                    height: 170,
                                                                                    menubar: false,
                                                                                    language: 'es',
                                                                                    force_br_newlines : true,
                                                                                    force_p_newlines : false,
                                                                                    forced_root_block : '',
                                                                                    plugins: ['lists image preview anchor', 'code', 'paste','link','emoticons'],
                                                                                    toolbar:
                                                                                        'styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                                                                    images_upload_handler: images_upload_handler,
                                                                                    toolbar_location: 'bottom'
                                                                                }"
                                                                            />
                                                                        </fieldset>
                                                                    </div>
                                                                    <div class="ii3 d-flex align-center">
                                                                        <v-icon class="ml-0 mr-2 icon_size" color="black"
                                                                                @click="deleteInstruction(instruction, i)">
                                                                            mdi-delete
                                                                        </v-icon>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </transition-group>
                                                </draggable>
                                            </div>
                                        </v-col>
                                        <v-col cols="4">
                                            <span class="text_default mb-2">Previsualización</span>
                                            <div class="bx_prev_instructions">
                                                <div class="bx_dialog bxd_right" :style="{'backgroundColor': colorSelected, 'color': colorSelected}">
                                                    <div class="content_dialog" v-html="content_instruction"></div>
                                                </div>
                                            </div>
                                        </v-col>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                            <v-btn color="primary" outlined @click="addInstruction" class="btn_add_instruction">
                                                <v-icon class="icon_size">mdi-plus-circle</v-icon>
                                            </v-btn>
                                        </v-col>
                                    </v-row>
                                </v-card-text>
                            </v-card>
                        </v-stepper-content>
                    </v-stepper-items>
                    <v-stepper-header class="stepper_dots">
                        <v-stepper-step step="1" :complete="stepper_box > 1">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="2" :complete="stepper_box > 2">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="3" :complete="stepper_box > 3">
                            <v-divider></v-divider>
                        </v-stepper-step>
                        <v-stepper-step step="4">
                        </v-stepper-step>
                    </v-stepper-header>
                </v-stepper>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="prevStep"
                    @confirm="nextStep"
                    @confirmBtnExtra="confirmAndContinueLater"
                    :cancelLabel="cancelLabel"
                    :confirmLabel="confirmLabel"
                    :disabled_next="disabled_btn_next"
                    :showBtnExtra="showBtnExtra && !process.config_completed"
                    :disabled_btn_extra="disabled_btn_next"
                    labelBtnExtra="Guardar y continuar luego"
                    />
            </v-card-actions>
        </v-card>
        <!-- <DefaultAlertDialog
            :ref="modalAlert.ref"
            :options="modalAlert"
            :confirmLabel="modalAlert.confirmLabel"
            :hideCancelBtn="modalAlert.hideCancelBtn"
            @onConfirm ="modalAlertClose()"
            @onCancel ="modalAlertClose()"
        >
            <template v-slot:content> {{ modalAlert.contentText }}</template>
        </DefaultAlertDialog> -->

        <ModalAvatarsRepository
            :ref="modalAvatarsRepository.ref"
            v-model="modalAvatarsRepository.open"
            :width="'650px'"
            @onCancel="modalAvatarsRepositoryClose()"
            @onConfirm="changeImgGuia"
        />
        <ModalUploadImageResize
            :ref="modalUploadImageResize.ref"
            v-model="modalUploadImageResize.open"
            :width="'500px'"
            @onCancel="closeFormModal(modalUploadImageResize)"
            @onConfirm="addIconFinishedOnboarding"
        />
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import DefaultButtonModalSteps from '../../globals/DefaultButtonModalSteps.vue';
import PreviewMap from './PreviewMap.vue';
import ModalAvatarsRepository from './ModalAvatarsRepository.vue'
import Editor from "@tinymce/tinymce-vue";
import ModalUploadImageResize from './ModalUploadImageResize';

export default {
    components: {
    draggable,
    DefaultButtonModalSteps,
    PreviewMap,
    ModalAvatarsRepository,
    editor: Editor,
    ModalUploadImageResize
},
    props: {
        value: Boolean,
        width: String,
        // process: Object,
        limitOne: {
            type:Boolean,
            default:false
        },
        tabs_title:{
            type: String,
            default: 'Segmentación'
        },
    },
    data() {
        return {
            list_icons_finished_onboarding: [],
            process: {
                instructions: [],
                subworkspaces: [],
                description: ''
            },
            modalDateOptions: {
                ref: 'DateEvent',
                open: false,
            },
            modalDateOptions2: {
                ref: 'DateEvent',
                open: false,
            },
            content_instruction: '',
            step_title: '',
            // step 3
            tab_preview_images: 'mobile',
            logo_selected: null,
            fondo_mobile_selected: null,
            fondo_web_selected: null,
            logo_cropped: null,
            fondo_mobile_cropped: null,
            fondo_web_cropped: null,
            // step 4
            menuPicker: false,
            menuImparPicker: false,
            menuParPicker: false,
            colorPicker: '#FE141F',
            colorDefault: '#5458EA',
            colorSelected: '#5458EA',
            colorMapaSelected: '#27f748',
            colorImparPicker: '#27F748',
            colorParPicker: '#8BFC89',
            icon_finished_selected_name: '',

            modalAvatarsRepository: {
                ref: 'ModalAvatarsRepository',
                open: false,
                endpoint: '',
            },
            modalUploadImageResize: {
                ref: 'ModalUploadImageResize',
                open: false,
                endpoint: '',
            },
            // steps
            disabled_btn_next: true,
            showBtnExtra: true,
            stepper_box_btn1: true,
            stepper_box_btn2: true,
            stepper_box_btn3: false,
            stepper_box_btn4: false,
            stepper_box: 1,
            cancelLabel: "Cancelar",
            confirmLabel: "Continuar",
            list_segments:[],
            sections: {
                showAdvancedOptions: false
            },
            modalDateStart: {
                open: false,
            },
            modalDateEnd:{
                open: false,
            },
            drag: false,
            expand_cursos: true,
            actividades_expanded: [],
            tipo_actividades: [
                {
                    text: "Se califica al alumno",
                    value: "trainer_user"
                },
                {
                    text: "Se califica al entrenador",
                    value: "user_trainer"
                }
            ],
            dialog: false,
            file: null,
            search_text: null,
            results_search: [],
            disabled_add_courses: false,
            isLoading: false,
            messageActividadesEmpty: false,
            formRules: {
                titulo_descripcion: [
                    v => !!v || 'Campo requerido',
                    v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
                ],
                actividad: [
                    v => !!v || 'Campo requerido',
                ],
            },
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Se califica al alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
            selects: {
                subworkspaces: [],
                type_checklist: [
                    {"id": "libre", "name": "Libre"},
                    {"id": "curso", "name": "Por curso"},
                ]
            },
            type_checklist: "libre",
            tabs: null,
            steps: 0,
            // total: 0,
            total: [],

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                name: null,
                type_checklist: null
            },
            resource: {},
            segments: [{
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            }],
            segment_by_document: {
                id: `new-segment-${Date.now()}`,
                type_code: '',
                criteria_selected: []
            },
            criteria: [],
            courseModules: [],
            modulesSchools: [],

            modalInfoOptions: {
                ref: 'SegmentAlertModal',
                open: false,
                title: null,
                resource:'data',
                hideConfirmBtn: true,
                persistent: true,
                cancelLabel: 'Entendido'
            },
            stackModals: { continues: [],
                backers: [] },
            criteriaIndexModal: 0,

            segment_by_document_clean: false,
            rules: {
                title: this.getRules(['required', 'max:200']),
                description: this.getRules(['required', 'max:350']),
                subworkspaces: this.getRules(['required']),
            }
            // data segmenteacion
        };
    },
    computed: {
        swatchStyle() {
            const { colorPicker, menuPicker } = this
            return {
                backgroundColor: colorPicker,
                borderRadius: menuPicker ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        },
        swatchStyleImpar() {
            const { colorImparPicker, menuImparPicker } = this
            return {
                backgroundColor: colorImparPicker,
                borderRadius: menuImparPicker ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        },
        swatchStylePar() {
            const { colorParPicker, menuParPicker } = this
            return {
                backgroundColor: colorParPicker,
                borderRadius: menuParPicker ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        },
        backgroundColorSelected(){
            const { colorSelected, colorDefault, colorPicker } = this
            return {
                backgroundColor: colorSelected != colorDefault ? colorPicker : colorDefault,
                color: colorSelected != colorDefault ? colorPicker : colorDefault
            }
        }
    },
    async mounted() {
        // this.addInstruction()
    },
    watch: {
        logo_cropped: {
            handler(n, o) {
                let vue = this;
            },
            deep: true
        },
        colorPicker: {
            handler(n, o) {
                let vue = this;
                vue.colorSelected = n;
            },
            deep: true
        },
        colorImparPicker: {
            handler(n, o) {
                let vue = this;
                vue.colorMapaSelected = n;
            },
            deep: true
        },
        colorParPicker: {
            handler(n, o) {
                let vue = this;
                vue.colorMapaSelected = n;
            },
            deep: true
        },
        process: {
            handler(n, o) {
                let vue = this;

                if(vue.stepper_box == 1) {
                    vue.stepper_box_btn1 = !(vue.validateRequired(vue.process.title) && vue.validateRequired(vue.process.subworkspaces) && vue.validateRequired(vue.process.description) && vue.validateRequired(vue.process.starts_at) && vue.process.description.length <= 350);
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                }
                else if(vue.stepper_box == 2){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn2;
                }
                else if(vue.stepper_box == 3){
                    vue.disabled_btn_next = vue.stepper_box_btn3;
                }
                else if(vue.stepper_box == 4){
                    let errors = vue.showValidateActividades()
                    vue.stepper_box_btn4 = false; //validarr subida de imagenes
                    vue.disabled_btn_next = vue.stepper_box_btn4;
                }
            },
            deep: true
        },
        stepper_box: {
            handler(n, o) {
                let vue = this;
                vue.confirmLabel = "Continuar";
                vue.cancelLabel = "Retroceder";
                vue.showBtnExtra = true

                if(vue.stepper_box == 1) {
                    if((vue.validateRequired(vue.process.title) && vue.validateRequired(vue.process.subworkspaces) && vue.validateRequired(vue.process.description) && vue.validateRequired(vue.process.starts_at) && vue.process.description.length <= 350)) {
                        vue.stepper_box_btn1 = false;
                    }
                    vue.disabled_btn_next = vue.stepper_box_btn1;
                    vue.step_title = ''
                    vue.cancelLabel = "Cancelar";
                }
                else if(vue.stepper_box == 2){
                    vue.disabledBtnModal()
                    vue.disabled_btn_next = vue.stepper_box_btn2;
                    vue.step_title = '> Personalización'
                }
                else if(vue.stepper_box == 3){
                    vue.disabled_btn_next = vue.stepper_box_btn3;
                    vue.step_title = '> Personalización'
                }
                else if(vue.stepper_box == 4){
                    let errors = vue.showValidateActividades()
                    vue.stepper_box_btn4 = false; //validarr subida de imagenes
                    vue.disabled_btn_next = vue.stepper_box_btn4;
                    vue.step_title = '> Personalización > Instructivo'
                    vue.confirmLabel = "Guardar";
                    vue.showBtnExtra = false
                }

                if(vue.process.instructions != null && vue.process.instructions.length > 0){
                    // vue.process.instructions.forEach(el => {
                    //     vue.instructionSelected(el.instruction)
                    // })
                    vue.instructionSelected(vue.process.instructions[0].description);
                }
            },
            deep: true
        },
        'process.count_absences': {
            handler(n, o) {
                let vue = this;
                if(!n) {
                    vue.process.limit_absences = false
                    vue.process.absences = ''
                }else{
                    vue.process.limit_absences = vue.process.absences == ''
                }
            },
            deep: true
        },
        'process.limit_absences': {
            handler(n, o) {
                let vue = this;
                if(n) {
                    vue.process.absences = ''
                }
            },
            deep: true
        },
        'process.absences': {
            handler(n, o) {
                let vue = this;
                if(n) {
                    vue.process.limit_absences = false
                }else {
                    vue.process.limit_absences = true
                }
            },
            deep: true
        }
    },
    methods: {
        maxCharacters(input) {
            let vue = this
        },
        classSelectIconOnboarding(ref_image) {
            let vue = this
            return vue.icon_finished_selected_name == ref_image ? 'selected' : ''
        },
        selectIconOnboarding(ref_image) {
            let vue = this
            vue.$nextTick(() => {
                vue.process.icon_finished_selected = vue.$refs[ref_image].src
                vue.process.icon_finished_selected_name = ref_image
                vue.icon_finished_selected_name = ref_image
            })
        },
        selectIconOnboardingUpload(url, name) {
            let vue = this
            vue.$nextTick(() => {
                vue.process.icon_finished_selected = url
                vue.process.icon_finished_selected_name = name
                vue.icon_finished_selected_name = name
            })
        },
        images_upload_handler(blobInfo, success, failure) {
            let vue = this
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            vue.$http
                .post("/upload-image/induccion_instrucciones", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        addIconFinishedOnboarding(data) {
            let vue = this
            if(data.logo_cropped)
                vue.list_icons_finished_onboarding.push(data)

            vue.closeFormModal(vue.modalUploadImageResize)
        },
        changeImgGuia(data, name) {
            let vue = this

            if(data.logo_cropped) {
                vue.$nextTick(() => {
                    vue.process.image_guia = data.logo_cropped
                    vue.process.image_guia_name = 'img_cropped'
                })
            }
            else if(data.url) {
                vue.$nextTick(() => {
                    vue.process.image_guia = data.url
                    vue.process.image_guia_name = 'img_uploaded'
                })
            } else {
                vue.$nextTick(() => {
                    vue.process.image_guia = data
                    vue.process.image_guia_name = name
                })
            }
            vue.closeFormModal(vue.modalAvatarsRepository)
        },

        modalAvatarsRepositoryClose() {
            let vue = this
            vue.modalAvatarsRepository.open = false
        },
        modalAlertClose() {
            let vue = this
            vue.modalAlert.open=false
        },
        instructionSelected(value) {
            let vue = this
            vue.content_instruction = value
        },
        changeLogoSelected($event) {
            let vue = this
            if($event)
            {
                vue.logo_selected = $event
            }
        },
        changeColorSelected() {
            let vue = this
            vue.menuPicker = !vue.menuPicker
            vue.colorSelected = vue.colorPicker
        },
        changeColorImparSelected() {
            let vue = this
            vue.menuImparPicker = !vue.menuImparPicker
            vue.colorMapaSelected = vue.colorImparPicker
        },
        changeColorParSelected() {
            let vue = this
            vue.menuParPicker = !vue.menuParPicker
            vue.colorMapaSelected = vue.colorParPicker
        },
        changeAvatarSelected() {
            let vue = this
            vue.modalAvatarsRepository.open = true
        },
        rep(){
            let vue = this
        },
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            console.log(vue.process);

            if(vue.stepper_box == 1){
                vue.stepper_box = 2;
            }
            else if(vue.stepper_box == 2){
                vue.stepper_box = 3;
            }
            else if(vue.stepper_box == 3){
                vue.stepper_box = 4;
            }
            else if(vue.stepper_box == 4){
                vue.process.config_completed = true
                vue.confirm();
            }
        },
        prevStep(){
            let vue = this;
            if(vue.stepper_box == 1){
                vue.closeModal();
            }
            else if(vue.stepper_box == 2){
                vue.stepper_box = 1;
            }
            else if(vue.stepper_box == 3){
                vue.stepper_box = 2;
            }
            else if(vue.stepper_box == 4){
                vue.stepper_box = 3;
            }
        },
        confirmAndContinueLater() {
            let vue = this;
            vue.confirm();
        },
        disabledBtnModal() {
            let vue = this;
            vue.stepper_box_btn2 = false;
        },
        setActividadesHasErrorProp() {
            let vue = this
            if (vue.checklist.checklist_actividades) {
                vue.checklist.checklist_actividades.forEach(el => {
                    el = Object.assign({}, el, {hasErrors: false})
                })
            }
        },
        closeModal() {
            let vue = this;
            vue.expand_cursos = true;
            vue.actividades_expanded = [];
            vue.search_text = null;

            vue.stepper_box = 1
            vue.tab_preview_images = 'mobile'
            vue.logo_selected = null
            vue.fondo_mobile_selected = null
            vue.fondo_web_selected = null
            vue.resource = {}
            vue.process = {
                instructions: [],
                subworkspaces: [],
                description: ''
            }

            vue.colorPicker = '#FE141F'
            vue.colorSelected = '#5458EA'
            vue.colorMapaSelected = '#27f748'
            vue.colorImparPicker = '#27F748'
            vue.colorParPicker = '#8BFC89'

            vue.resetValidation()
            vue.$emit("onCancel");
        },
        resetValidation() {
            let vue = this;
            // vue.search_text = null
            // vue.results_search = []
        },
        async confirm() {
            let vue = this;
            if(vue.process.subworkspaces.length == 0){
                vue.showAlert('Es necesario seleccionar al menos 1 módulo','warning');
                // vue.loadingActionBtn = false
                // this.hideLoader()
                return;
            }

            // const allIsValid = vue.moreValidaciones()

            // if (allIsValid == 0)
            vue.process.color_selected = vue.colorSelected;
            vue.process.color_map_even = vue.colorParPicker
            vue.process.color_map_odd = vue.colorImparPicker

            if(vue.logo_cropped)
            {
                let logotipo_blob = await fetch(vue.logo_cropped).then(res => res.blob());
                vue.process.logotipo = logotipo_blob;
            }
            if(vue.fondo_mobile_cropped)
            {
                let fondo_mobile_blob = await fetch(vue.fondo_mobile_cropped).then(res => res.blob());
                vue.process.fondo_mobile = fondo_mobile_blob;
            }
            if(vue.fondo_web_cropped)
            {
                let fondo_web_blob = await fetch(vue.fondo_web_cropped).then(res => res.blob());
                vue.process.fondo_web = fondo_web_blob;
            }
            if(vue.process.image_guia)
            {
                let image_guia_name = vue.process.image_guia_name &&
                (vue.process.image_guia_name.indexOf('img_cropped') !== -1 ||
                vue.process.image_guia_name.indexOf('img_guide_') !== -1)

                if(image_guia_name) {
                    vue.process.img_guia_blob = await fetch(vue.process.image_guia).then(res => res.blob());
                }
                else {
                    vue.process.image_guia = vue.process.image_guia.split('/induccion/')
                    vue.process.image_guia = vue.process.image_guia && vue.process.image_guia.length > 0 ? vue.process.image_guia[1] : ''
                }
                vue.process.image_guide_name = vue.process.image_guia_name
            }
            if(vue.process.icon_finished_selected)
            {
                let icon_finished_selected_name = vue.process.icon_finished_selected_name &&
                (vue.process.icon_finished_selected_name.indexOf('img_onb_') !== -1 ||
                vue.process.icon_finished_selected_name.indexOf('name_icon_') !== -1)

                if(icon_finished_selected_name) {
                    vue.process.icon_finished_blob = await fetch(vue.process.icon_finished_selected).then(res => res.blob());
                }
                else {
                    vue.process.icon_finished = vue.process.icon_finished_selected.split('/induccion/')
                    vue.process.icon_finished = vue.process.icon_finished && vue.process.icon_finished.length > 0 ? vue.process.icon_finished[1] : ''
                }
                vue.process.icon_finished_name = vue.process.icon_finished_selected_name
            }

            vue.$emit("onConfirm", vue.process);
        },
        showValidateActividades() {
            let vue = this
            let errors = 0;

            // if( vue.checklist.checklist_actividades.length > 0 ) {
            //     vue.checklist.checklist_actividades.forEach((el, index) => {
            //         el.hasErrors = !el.activity || el.activity === ''
            //         if(el.hasErrors) errors++;
            //     })
            // }
            return errors;
        },
        // moreValidaciones() {
        //     let vue = this
        //     let errors = 0

        //     let hasActividadEntrenadorUsuario = false;
        //     // vue.checklist.checklist_actividades.map(actividad=>{
        //     //    if( actividad.type_name=='trainer_user') hasActividadEntrenadorUsuario=true;
        //     // });
        //     if(!hasActividadEntrenadorUsuario){
        //         this.modalAlert.open= true;
        //        errors++
        //     }
        //     return errors > 0
        // },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        deleteInstruction(instruction, index) {
            let vue = this;
            if (String(instruction.id).charAt(0).includes('n')) {
                vue.actividades_expanded = []
                vue.process.instructions.splice(index, 1);
                return
            }
            axios
                .post(`/entrenamiento/checklists/delete_actividad_by_id`, instruction)
                .then((res) => {
                    if (res.data.error) {
                        vue.$notification.warning(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    } else {
                        vue.actividades_expanded = []
                        vue.process.instructions.splice(index, 1);
                        vue.$notification.success(`${res.data.msg}`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                    }
                })
                .catch((err) => {
                    console.err(err);
                });
        },
        addInstruction() {
            let vue = this;
            const newID = `n-${Date.now()}`;
            const newInstruction = {
                id: newID,
                description: "",
                active: 1,
                hasErrors: false
            };
            vue.process.instructions.push(newInstruction);
        },
        search() {
            clearTimeout(this.timeout);
            let vue = this;
            if (vue.search_text == null || vue.search_text === "") return;
            if (vue.isLoading) return;
            this.timeout = setTimeout(function () {
                vue.isLoading = true;
                const data = {
                    filtro: vue.search_text,
                };
                axios
                    .post(`/entrenamiento/checklists/buscar_curso`, data)
                    .then((res) => {
                        vue.results_search = res.data.data;
                        // vue.$nextTick(() => {
                        //     vue.$refs.resultSearch.focus()
                        //     vue.$refs.resultSearch.isMenuActive = true
                        //     vue.$refs.resultSearch.isMenuActive = true;
                        // })
                        setTimeout(() => {
                            vue.isLoading = false;
                        }, 1500);
                    })
                    .catch((err) => {
                        console.log(err);
                        vue.isLoading = false;
                    });
            }, 1000);
        },
        removeCurso(curso, index) {
            let vue = this;
            vue.results_search.push(curso)
            vue.checklist.courses.splice(index, 1)
            if(vue.checklist.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }

        },
        seleccionarCurso(curso, index) {
            let vue = this;
            vue.checklist.courses.push(curso)
            vue.results_search.splice(index, 1)
            if(vue.checklist.courses.length >= 2) {
                vue.disabled_add_courses = true;
            } else {
                vue.disabled_add_courses = false;
            }
        },
        getNewSegment(type_code) {
            return {
                id: `new-segment-${Date.now()}`,
                type_code,
                criteria_selected: []
            };
        },
        async addSegmentation(type_code) {
            let vue = this;
            vue.segments.push(this.getNewSegment(type_code));

            vue.steps = vue.segments.length - 1;
        },
        borrarBloque(segment) {
            let vue = this;
            // const isNewSegment = segment.id.search("new") !== -1;
            // if (vue.segments.length === 1 && !isNewSegment) return;

            vue.segments = vue.segments.filter((obj, idx) => {
                return obj.id != segment.id;
            });
        },
        isCourseSegmentation() {
            return this.model_type === 'App\\Models\\Course'
        },
        async changeTypeChecklist()
        {
            let vue = this;
            vue.type_checklist = vue.resource.type_checklist;
        },

        async loadData(resource) {
            let vue = this

            vue.$nextTick(() => {
                vue.process = Object.assign({}, vue.process, resource)
                if(resource)
                {
                    if(resource.color) {
                        if(resource.color != vue.colorDefault) {
                            vue.colorPicker = resource.color
                            vue.colorSelected = resource.color
                        }
                    }
                    vue.colorParPicker = resource.color_map_even ? resource.color_map_even : vue.colorParPicker
                    vue.colorImparPicker =  resource.color_map_odd ? resource.color_map_odd : vue.colorImparPicker

                    if(resource.repository.list_icon_final.length > 0)
                        vue.list_icons_finished_onboarding = resource.repository.list_icon_final

                    if(resource.instructions.length == 0)
                    {
                        vue.addInstruction()
                    }
                }
                else
                {
                    vue.addInstruction()
                }
            })
        },
        async loadSelects() {
            let vue = this
            let url = `/procesos/form-selects`

            vue.$http.get(url)
                .then(({data}) => {
                    console.log(data.data.modules);
                    vue.selects.subworkspaces = data.data.modules
                })
        },
        closeModalOutside() {
            let vue = this
            // if (!vue.options.persistent)
            //     vue.$emit('onCancel')
        },
    }
};
</script>
<style lang="scss">
.list-cursos-carreras {
    width: 500px;
    white-space: unset;
}

.ghost {
    opacity: 0.5;
    background: #c8ebfb;
}

.flip-list-move {
    transition: transform 0.5s;
}

.no-move {
    transition: transform 0s;
}

.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}

//.v-input__icon {
//    padding-bottom: 12px;
//}

.v-icon.v-icon.v-icon--link {
    color: #1976d2;
}

.icon_size .v-icon.v-icon {
    font-size: 31px !important;
}

.lista-boticas {
    list-style-type: disc;
    -webkit-columns: 3;
    -moz-columns: 3;
    columns: 3;
    list-style-position: inside;
}

.fade-enter-active, .fade-leave-active {
    transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}

.custom-draggable, .custom-draggable span {
    width: 100%;
}

.v-expansion-panel-header {
    padding: 0.7rem !important;
}
.bx_steps .v-text-field__slot label.v-label,
.bx_steps .v-select__slot label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.box_info_checklist_1 {
    text-align: justify;
}
.v-stepper__header.stepper_dots {
    justify-content: center;
    height: initial;
}
.v-stepper__header.stepper_dots .v-divider {
    margin: 0;
    flex: auto;
    border-color: #5458ea !important;
    border-width: 2px;
    width: 30px;
    min-width: 30px;
    max-width: 30px;
}
.v-stepper__header.stepper_dots .v-stepper__step {
    padding: 10px 0;
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step span.v-stepper__step__step {
    margin: 0;
}
.v-stepper__header.stepper_dots .v-stepper__step:hover {
    background: none;
}
.v-stepper.stepper_box {
    box-shadow: none;
}
.txt_desc textarea {
    min-height: 280px;
}
.v-tab span.title_sub {
    font-size: 16px;
    color: #B9E0E9;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub,
span.title_sub {
    font-size: 16px;
    color: #5458EA;
    display: flex;
    justify-content: center;
    margin: 12px 0;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    position: relative;
    text-transform: initial;
    letter-spacing: 0.1px;
}
.v-tab.v-tab--active span.title_sub:after,
span.title_sub:after {
    content: '';
    border-bottom: 2px solid #5458EA;
    width: 112px;
    position: absolute;
    bottom: -2px;
}
.v-tab span.title_sub:after  {
    content: none;
}
button.btn_secondary {
    background: none !important;
    border: none;
    box-shadow: none;
}
button.btn_secondary span.v-btn__content {
    color: #5458EA;
    font-weight: 700;
    font-size: 12px;
    font-family: "Nunito", sans-serif;
}
button.btn_secondary span.v-btn__content i{
    font-size: 14px;
    line-height: 1;
}
.divider_light{
    border-color: #94DDDB !important;
}
.item-draggable.activities {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    margin: 10px 0;
    border-radius: 8px;
}
.item-draggable.activities textarea,
.no-white-space .v-select__selection--comma,
.item-draggable.activities .toggle_text_default label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 12px;
}
.no-white-space .v-select__selection--comma {
    white-space: initial;
    line-height: 13px;
    font-size: 13px;
}
.item-draggable.activities textarea {
    font-size: 13px;
}
.no-white-space .v-input__append-inner .v-input__icon {
    padding: 0;
}
.item-draggable.activities .default-toggle.default-toggle.v-input--selection-controls {
    margin-top: initial !important;
}
// .box_resultados,
// .box_seleccionados {
//     height: 130px;
//     overflow-y: auto;
//     border-radius: 8px;
//     border: 1px solid #D9D9D9;
//     padding: 10px 0;
// }
// .box_resultados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before,
// .box_seleccionados .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before {
//     opacity: 0;
// }
// .bx_message {
//     display: flex;
//     justify-content: center;
//     align-items: center;
//     height: 100%;
// }
span.v-stepper__step__step {
    color: #5458ea !important;
    background-color: #5458ea !important;
    border-color: #5458ea !important;
    position: relative;
}
span.v-stepper__step__step:before {
    content: '';
    background: #fff;
    height: 17px;
    width: 17px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
span.v-stepper__step__step:after {
    content: '';
    background-color: #5458ea;
    height: 6px;
    width: 6px;
    left: 50%;
    top: 50%;
    position: absolute;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step,
.v-stepper__step.v-stepper__step--inactive span.v-stepper__step__step:after,
.v-stepper__step.v-stepper__step--inactive .v-divider {
    color: #E4E4E4 !important;
    background-color: #E4E4E4 !important;
    border-color: #E4E4E4 !important;
}
.bx_type_checklist .v-input__icon.v-input__icon--append,
.bx_steps .v-input__icon.v-input__icon--append {
    margin: 0;
    padding: 0;
}
.bx_steps .v-input__slot {
    min-height: 40px !important;
}
.bx_steps .v-text-field--outlined .v-label {
    top: 10px;
}
// .bx_steps .v-btn--icon.v-size--default {
//     height: 22px;
//     width: 22px;
// }
.bx_steps .v-select__slot,
.v-dialog.v-dialog--active .bx_steps .v-select--is-multi.v-autocomplete .v-select__slot {
    padding: 0 !important;
}
//.bx_steps .v-text-field__details {
   // display: none;
//}
.bx_step1 .default-toggle {
    margin-top: 3px !important;
}
.bx_step1 .default-toggle .v-input__slot label.v-label {
    color: #434D56;
    font-weight: 400;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-size: 13px;
}
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:before,
.v-stepper__step.v-stepper__step--complete span.v-stepper__step__step:after {
    content: initial;
}
.bx_steps .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 10px !important;
}
.v-card__actions.actions_btn_modal button.default-modal-action-button.btn_back.v-btn.v-btn--flat span.v-btn__content {
    color: #5458ea;
}
.v-menu.bx_calendar_top .v-menu__content {
    bottom: 0px;
    top: initial !important;
    right: calc(100% - 10px);
    left: initial !important;
    transform-origin: right bottom !important;
}
.bx_seg .v-select__slot .v-input__append-inner,
.bx_steps .bx_seg .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 6px !important;
}
.border-error .v-input__slot fieldset {
    border-color: #FF5252 !important;
}

.modal_edit_process {

    .txt_desc textarea {
        min-height: auto;
        font-size: 14px;
    }

    .bx_input_inasistencias input {
        border: 1px solid #D9D9D9;
        border-radius: 8px;
        max-width: 100px;
        height: 40px;
        text-align: center;
    }
    .btn_add_instruction {
        border: none;
    }
    .bx_instructions_list {
        .bx_overflow {
            max-height: 300px;
            overflow-y: auto;
            padding: 0 20px;
        }
    }
    .dropzone {
        min-height: 140px !important;
        padding: 0 20px !important;
        .dz-message {
            margin: 0 !important;
            padding-top: 26px !important;
        }
        .icon_upload {
            margin-bottom: 13px;
        }
    }
    .vue-dropzone, .vue-dropzone:hover {
        border: none !important;
    }
    .box_preview {
        border: 1px solid #C4C4C4;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 4px;
        span.text_default {
            font-size: 16px;
            text-align: center;
            max-width: 200px;
        }
        .tpl_preview_mobile {
            text-align: center;
            width: 140px;
            min-height: 230px;
            .bx_imgs_pm {
                position: relative;
                border-radius: 6px;
                overflow: hidden;
                margin: 0 20px;
                .bx_fondo_pm img {
                    max-width: 100%;
                }
                .bx_logo_pm {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    max-width: 50px;
                    img {
                        max-width: 100%;
                    }
                }
            }
            span.text_default {
                font-size: 11px !important;
            }
        }
        .tpl_preview_web {
            text-align: center;
            .bx_imgs_pm {
                position: relative;
                border-radius: 6px;
                overflow: hidden;
                .bx_fondo_pm img {
                    max-width: 100%;
                }
                .bx_logo_pm {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    max-width: 50px;
                    img {
                        max-width: 100%;
                    }
                }
                span.text_default {
                    background-color: #fff;
                    position: absolute;
                    bottom: 10px;
                    right: 10px;
                    padding: 2px 10px;
                    font-size: 11px !important;
                }
            }
        }
    }
    .btns_preview_type button.v-btn{
        border-radius: 15px;
    }
    .bx_steps.bx_step1 .v-input--selection-controls .v-input__slot>.v-label,
    .bx_steps.bx_step1 .v-input--selection-controls .v-radio>.v-label {
        margin-bottom: 0;
        font-family: "Nunito", sans-serif;
        font-size: 13px;
        font-weight: 400;
        color: #2C2D2F;
    }
    // step 4 - colores
    .box_select_colors {
        display: flex;
        .item_color_onb {
            margin-right: 3px;
            text-align: center;
            cursor: pointer;
            border: 1px solid transparent;
            padding: 5px;
            border-radius: 10px;
            &.selected {
                border-color: #5458EA;
            }
            span.text_default {
                font-family: "Nunito", sans-serif;
                font-size: 11px;
                color: #434D56;
            }
            .bg_color_item button {
                .icon_edit {
                    opacity: 0;
                    transition: .5s;
                }
                &:hover .icon_edit {
                    opacity: 1;
                    transition: .5s;
                }
            }
            .bg_color_item button,
            .bg_color_item .color_d {
                height: 75px !important;
                width: 55px !important;
                min-width: auto !important;
                border-radius: 8px !important;
            }
            .bg_color_item.bg_color_item_default .color_d {
                background-color: #5458EA;
            }
        }
        &.colors_maps{
            .item_color_onb {
                .bg_color_item button,
                .bg_color_item .color_d {
                    height: 55px !important;
                }

            }
        }
    }
    .box_select_icons {
        display: flex;
        flex-wrap: wrap;
        column-gap: 10px;
        row-gap: 10px;
        .item_icono_onb {
            .bg_icon_item {
                background-color: #5458EA;
                border-radius: 50%;
                width: 65px;
                height: 65px;
                display: flex;
                justify-content: center;
                align-items: center;
                opacity: .6;
                cursor: pointer;
                overflow: hidden;
                border: 1px solid #fff;
                img {
                    max-width: 100%;
                }
                &:hover{
                    opacity: 1;
                }
                &.selected {
                    opacity: 1;
                    border-color: #5458EA;
                }
            }
        }
    }
    .bx_preview_colors {
        display: flex;
        column-gap: 10px;
        .bx_preview_map {
            max-width: 50%;
            flex: 1;
            box-shadow: 2px 2px 10px #dfdfdf;
            padding: 15px 4px;
            svg {
                max-width: 100%;
                height: auto;
            }
        }
        .bg_instructions_profile {
            position: relative;
            flex: 1;
            box-shadow: 2px 2px 10px #dfdfdf;
            padding: 15px 4px;
            .bg_bubble {
                position: absolute;
                background-color: #5458EA;
                color: #5458EA;
                height: 180px;
                width: 90%;
                border-radius: 10px;
                max-width: 120px;
                left: 50%;
                transform: translateX(-50%);
                top: 20px;
                &:after {
                    content: '';
                    width: 0px;
                    height: 0px;
                    border-style: solid;
                    border-width: 0 8px 15px 8px;
                    border-color: transparent transparent currentColor transparent;
                    transform: rotate(180deg);
                    position: absolute;
                    bottom: -15px;
                    left: 14px;
                }
            }
            .bx_change_img_profile {
                position: absolute;
                top: 170px;
                right: 18px;
                text-align: center;
                .bx_img_profile {
                    max-width: 80px;
                    text-align: center;
                    position: relative;
                    border-radius: 50%;
                    height: 80px;
                    width: 80px;
                    overflow: hidden;
                    img {
                        max-width: 100%;
                    }
                    .icon_edit {
                        position: absolute;
                        background-color: #434D56CC;
                        left: 0;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        border-radius: 50%;
                        align-items: center;
                        justify-content: center;
                        display: none;
                        cursor: pointer;
                    }
                    &:hover{
                        > img {
                            filter: blur(4px);
                        }
                        .icon_edit {
                            display: flex;
                        }
                    }
                }
                span.text_default {
                    font-size: 11px;
                }
            }
        }
    }
    .bx_preview_change_logo .dropzone .dz-preview.dz-image-preview .dz-image div {
        background-size: initial !important;
    }
    .bx_prev_instructions {
        border: 1px solid #C4C4C4;
        padding: 20px 20px 25px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 4px;
        .bx_dialog {
            background: #57BFE3;
            min-height: 200px;
            border-radius: 16px;
            width: 220px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            padding: 20px 0;
            &:before {
                content: '';
                width: 0px;
                height: 0px;
                border-style: solid;
                border-width: 15px 10px 0px 10px;
                border-color: currentColor transparent;
                display: inline-block;
                bottom: -15px;
                position: absolute;
                left: 25px;
            }
            .content_dialog {
                color: #fff;
                max-width: 165px;
                font-weight: 400;
                font-family: "Nunito", sans-serif;
                line-height: 15px;
                font-size: 12px;
                img {
                    max-width: 100%;
                    height: auto !important;
                }
            }
        }
    }
    .item_instruction {
        display: flex;
        justify-content: space-between;
        padding: 15px 0;
        .ii3,
        .ii1 {
            width: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .ii2 {
            flex: 1;
        }
    }
    .bx_input_date {
        flex: 1;
        max-width: 195px;
    }
    .row_dates, .row_border {
        border: 1px solid #d9d9d9;
        padding: 10px;
        border-radius: 4px;
        width: 100%;
    }
    .row_dates {
        column-gap: 10px;
        .v-text-field--enclosed .v-input__append-inner,
        .v-text-field--enclosed .v-input__append-outer,
        .v-text-field--enclosed .v-input__prepend-inner {
            margin-top: 2px !important;
        }
    }
    .text_default.disabled, .disabled_label label.v-label, .bx_input_inasistencias.disabled input {
        color: #E4E4E4 !important;
    }
}
</style>
