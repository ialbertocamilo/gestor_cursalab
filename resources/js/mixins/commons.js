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
        },
        async cleanValidationsModal(modal, modalDefault) {
            let vue = this
            await vue.$nextTick(() => vue.modal = JSON.parse(JSON.stringify(modalDefault)))
        },
        async handleValidationsBeforeUpdate(errorObject, modalOptions, modalDefault) {
            let vue = this;
            if (errorObject.http_code !== 422) return;

            let validations = errorObject.data.validations;
            // console.log(validations)
            if (validations.list.length > 0) {
                // await vue.$nextTick(() => modalOptions = Object.assign({}, modalOptions, modalDefault))
                await vue.cleanValidationsModal(modalOptions, modalDefault)

                if (validations.type === 'validations-before-update') {
                    modalOptions.type = validations.type;
                    modalOptions.cancelLabel = !validations.show_confirm ? 'Entendido' : 'Cancelar';
                    modalOptions.hideConfirmBtn = !validations.show_confirm;

                    await vue.openFormModal(modalOptions, validations, 'validations-before-update', validations.title);
                }
            }
        },
        async handleValidationsAfterUpdate(data, modalOptions, modalDefault) {
            let vue = this;
            // await vue.$nextTick(() => modalOptions = Object.assign({}, modalOptions, modalDefault))
            // modalOptions = Object.assign({}, modalOptions, modalDefault)
            await vue.cleanValidationsModal(modalOptions, modalDefault)

            modalOptions.type = data.messages.type;
            modalOptions.hideCancelBtn = true;
            modalOptions.hideConfirmBtn = false;
            modalOptions.cancelLabel = 'Entendido';
            modalOptions.confirmLabel = 'Entendido';
            modalOptions.persistent = true;
            modalOptions.showCloseIcon = false;

            await vue.openFormModal(modalOptions, data.messages, 'validations-after-update', data.messages.title);
        },
        confirmValidationModal(modalOptions, redirectRoute = null, callbackWithoutValidating = () => {
        }) {
            // console.info("confirmValidationModal", modalOptions);
            if (modalOptions.type === 'validations-before-update') {
                if (!modalOptions.hideConfirmBtn) callbackWithoutValidating()
                modalOptions.open = false;
            }

            if (modalOptions.type === 'validations-after-update')
                if (redirectRoute) window.location.href = redirectRoute;
        }
    },
}
