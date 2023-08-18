<template>
    <div
        class="card-indicator"
        :class="path ? 'link' : '' "
        @click="navigateTo(path)"
    >
        <v-btn
            elevation="0"
            fab
            dark
            small
            :color="iconColor"
            :ripple="false"
            style="cursor:initial !important"
        >
            <v-icon dark v-text="icon"/>
        </v-btn>
        <div class="ml-2">
            <span class="amount-card-indicator ml-1">{{ amount }}</span>
            <p class="label-card-indicator ml-1">{{ label }}</p>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        icon: {
            type: String,
            required: true
        },
        iconColor: {
            type: String,
            default: 'primary'
        },
        amount: {
            required: true
        },
        label: {
            required: true
        },
        path: {
            type: String
        }
    },
    methods: {
        navigateTo(path) {

            if (path) {
                window.location.href = path;
            }

        }
    }
}
</script>

<style scoped>
    .link{
        cursor: pointer;
    }
</style>
