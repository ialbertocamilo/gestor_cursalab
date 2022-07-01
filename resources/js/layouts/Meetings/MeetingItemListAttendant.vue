<template>
    <div class="d-flex justify-content-between align-items-center">
        <div class="item-meeting-search-attendants-results">
            <v-btn icon @click="removeAttendant()" v-if="showDeleteButton">
                <v-icon small v-text="'mdi-trash-can'"/>
            </v-btn>
            <v-simple-checkbox v-if="showCheckbox"
                v-model="item.checked"
            />
            <DefaultLogoImage
                :image="attendant.usuario ? attendant.usuario.config.logo : attendant.module_logo"
                class="mx-2"
                max-width="70"
            />
            {{ attendant.usuario ? attendant.usuario.dni : attendant.dni }} -
            {{ attendant.usuario ? attendant.usuario.apellido_paterno : attendant.apellido_paterno }}
            {{ attendant.usuario ? attendant.usuario.apellido_materno : attendant.apellido_materno }},
            {{ attendant.usuario ? attendant.usuario.nombre : attendant.nombre }}
            <DefaultChip
                v-if="attendant.show"
                v-text="`${attendant.count} ${attendant.count === 1 ? 'reunión' : 'reuniones'}`"
                color=error
                class="ml-2"
                @onClick="openAttendantScheduledMeetingsModal"
                cursor_pointer
            />
        </div>
        <div class="item-options-meeting-search-attendants-results">
            <DefaultInfoTooltip text="Marcar como asistente" left small/>
            <v-switch
                dense
                class="default-toggle ml-2" inset hide-details="auto"
                v-model="attendant.type_id" :value="coHostAttendantId"/>
        </div>
    </div>
</template>

<script>
import ModalScheduledMeetings from "./ModalScheduledMeetings";
import DefaultLogoImage from "../../components/globals/DefaultLogoImage";
export default {
    components: {ModalScheduledMeetings, DefaultLogoImage },
    props: {
        coHostAttendantId: String | Number,
        attendant: {
            type: Object,
            required: true
        },
        showDeleteButton: {
            type: Boolean,
            default: false
        },
        showCheckbox: {
            type: Boolean,
            default: false
        },
    },
    methods: {
        removeAttendant() {
            let vue = this
            vue.$emit('removeAttendant')
        },
        openAttendantScheduledMeetingsModal(){
            let vue = this
            vue.$emit('viewAttendantScheduledMeetings')
        }
    }
}
</script>
