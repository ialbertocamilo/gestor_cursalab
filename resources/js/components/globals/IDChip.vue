<template>
    <v-tooltip
        :top="true"
        attach
    >
        <template v-slot:activator="{ on, attrs }">
            <div
                v-if="ID"
                class="wrapper">
                <div
                    :class="'chip ' + cssClass"
                    v-bind="attrs"
                    v-on="on">
                    <v-icon
                        :color="'#fff'"
                        :size="20">
                        mdi-chart-donut-variant
                    </v-icon>
                    <span class="ml-2">
                        ID: {{ ID }}
                    </span>
                </div>
                <v-icon
                    @click="copyID()"
                    :color="'#5458ea'"
                    :size="20"
                    class="ml-2">
                    {{ copyIcon }}
                </v-icon>
            </div>

        </template>
        <span>
                <b>{{ description }}</b>: {{ ID }}
            </span>
    </v-tooltip>
</template>

<script>
export default {
    props: {
        ID: {
            type: String,
            required: true
        },
        description: {
            type: String,
            required: true
        },
        cssClass: {
          type: String,
          required: false
        }
    },
    data() {
        return {
            copyIcon : 'mdi-content-copy'
        }
    },
    methods: {
        async copyID () {
            try {
                // Copy ID value

                await navigator.clipboard.writeText(this.ID);

                // Update copy icon

                this.copyIcon = 'mdi-check-circle'
                setTimeout(() => {
                    this.copyIcon = 'mdi-content-copy'
                }, 3000)

            } catch (err) {
                console.error('Could not copy text: ', err);
            }
        }
    }
}
</script>

<style scoped lang="scss">

    .wrapper {

        padding-left: 5px;
        min-width: 140px;
        display: inline-flex;
        align-items: center;

        .chip {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding-left: 10px;
            padding-right: 10px;
            background: #c893f7;
            color: white;
            height: 32px;
            border-radius: 32px;
        }
    }

</style>
