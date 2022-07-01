export default {
    data() {
        return {}
    },
    methods: {
        showSnackbar(snackbar, open, text, color = "success") {
            snackbar.open = open
            snackbar.text = text
            snackbar.color = color
        },

    }
}
