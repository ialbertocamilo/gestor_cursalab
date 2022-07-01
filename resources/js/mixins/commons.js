export default {
    methods: {
        openInNewTab(url) {
            if (url)
                window.open(url, '_blank').focus();
        },
        handle_function(function_name, item = null) {
            let vue = this
            if (!function_name) return false

            if (item)
                return vue[function_name](item)

            return vue[function_name]
        },
        activateOverlay(overlay) {
            let vue = this
            vue[overlay] = true;
        },
        inactivateOverlay(overlay) {
            let vue = this
            vue[overlay] = false;
        }


    },
}
