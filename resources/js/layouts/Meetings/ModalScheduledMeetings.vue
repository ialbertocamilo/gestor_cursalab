<template>
    <v-dialog
        v-model="options.open"
        :width="width"
        @click:outside="closeModal"
    >
        <v-card>
            <v-list>
                <v-list-item>
                    <DefaultLogoImage
                        :image="options.header.logo"
                        class="mx-3"
                        max-width="70"
                    />

                    <v-list-item-content>
                        <v-list-item-title>Reuniones ya agendadas</v-list-item-title>
                        <v-list-item-subtitle style="font-size: .75rem">{{ options.header.title }}</v-list-item-subtitle>
                    </v-list-item-content>

                    <v-list-item-action>
                        <v-btn
                            icon
                            @click="closeModal"
                        >
                            <v-icon v-text="'mdi-close'"/>
                        </v-btn>
                    </v-list-item-action>
                </v-list-item>
            </v-list>
            <DefaultDivider/>
            <v-list>
                <v-list-item v-for="meeting in options.meetings" :key="meeting.id">
                    <v-list-item-title>
                        <span> {{ meeting.name }}</span> -
                        <span> {{ meeting.date }}</span>
                    </v-list-item-title>
                </v-list-item>

            </v-list>
        </v-card>
    </v-dialog>
</template>


<script>
import DefaultLogoImage from "../../components/globals/DefaultLogoImage";

export default {
    components: {DefaultLogoImage},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: Number | String,
            default: '40vw'
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit("onClose")
        }
    }
}
</script>
