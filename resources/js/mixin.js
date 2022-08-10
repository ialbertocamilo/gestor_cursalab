const FileSaver = require("file-saver");

import mime from "mime-types";
import moment from "moment";
moment.locale("es");

const extensiones = {
    image: ["jpeg", "jpg", "png", "gif", "svg", "webp"],
    video: ["mp4", "webm", "mov"],
    audio: ["mp3","mpga"],
    pdf: ["pdf"],
    excel: ["xls", 'xlsx', 'csv'],
    scorm: ["zip", "scorm"]
};
const default_media_images = {
    video: "images/default-video-img_285.png",
    audio: "images/default-audio-img_119.png",
    pdf: "images/default-pdf-img_210.png",
    excel: "images/default-scorm-img_116.png",
    scorm: "images/default-scorm-img_116.png",
}

export default {
    data() {
        return {
            open_advanced_filter: false,
            mixin_multimedias: [
                {label: 'YouTube', icon: 'mdi mdi-youtube', type: 'youtube'},
                {label: 'Vimeo', icon: 'mdi mdi-vimeo', type: 'vimeo'},
                {label: 'Video', icon: 'mdi mdi-video', type: 'video'},
                {label: 'Audio', icon: 'mdi mdi-headset', type: 'audio'},
                {label: 'PDF', icon: 'mdi mdi-file-pdf-box', type: 'pdf'},
                {label: 'SCORM', icon: 'mdi mdi-file-compare', type: 'scorm'},
            ],
            mixin_extensiones: extensiones,
            mixin_default_media_images: default_media_images
        }
    },
    methods: {
        isValuesObjectEmpty(obj) {
            for (var key in obj) {
                if (obj[key] !== null && obj[key] != "")
                    return false;
            }
            return true;
        },
        emptyObj(obj) {
            return Object.entries(obj).length === 0
        },
        showLoader() {
            $('#pageloader').css('display', 'flex')
            // $('#pageloader').fadeIn()
        },
        hideLoader() {
            setTimeout(() => {
                // $('#pageloader').css('display', 'none')
                $('#pageloader').fadeOut()
            }, 800)
        },
        showAlert(msg, type = 'success', title = '') {
            let vue = this
            let options = {
                title: title,
                timer: 10,
                showLeftIcn: false,
                showCloseIcn: true,
            }
            console.log('showAlert')
            switch (type) {
                case 'primary':
                    vue.$notification.primary(msg, options);
                    break;
                case 'dark':
                    vue.$notification.dark(msg, options);
                    break;
                case 'success':
                    vue.$notification.success(msg, options);
                    break;
                case 'warning':
                    vue.$notification.warning(msg, options);
                    break;
                case 'error':
                    vue.$notification.error(msg, options);
                    break;
                default:
                    vue.$notification.success(msg, options);
                    break;
            }
        },
        optionDisabled(array, id) {
            return array.includes(id);
        },
        mostrarDataProcesosMasivos(data) {
            return {
                info: data.info,
                data_error: data.data_error,
                data_type_error: data.data_type_error,
            }
        },
        TypeOf(x) {
            // console.log('x')
            // console.log(x)
            if (x === null) {
                return 'null';
            }

            switch (typeof x) {
                case "undefined":
                    return "undefined";
                case "boolean":
                    return "boolean";
                case "number":
                    return "number";
                case "string":
                    return "string";
                default:
                    if (Array.isArray(x)) return "array";
                    return "object";
            }
        },
        consoleObjectTable(object, logTitle = "Table Log") {
            let tempObj = {};
            for (const prop in object) {
                let objValue = object[prop];
                tempObj[prop] = `${this.TypeOf(objValue)} => ${JSON.stringify(objValue)}`;
            }
            // console.info(logTitle);
            // console.table(tempObj);
        },
        infoMedia(item) {

            if (!item.ext) return false;

            let data = {
                title: item ? item.title : "",
                tipo: "archivo",
                color: "#455A64",
                preview: "/img/icon-file.svg"
            };
            if (item)
                if (extensiones.image.includes(item.ext.toLowerCase())) {
                    data.preview = `https://static.universidadcorporativafp.com.pe/${item.file}`;
                    data.tipo = "Imagen";
                    data.color = "#f6685e";
                } else if (extensiones.video.includes(item.ext)) {
                    data.preview = "/img/icon-video.svg";
                    data.tipo = "Video";
                    data.color = "#2196f3";
                } else if (extensiones.audio.includes(item.ext)) {
                    data.preview = "/img/icon-audio.svg";
                    data.tipo = "Audio";
                    data.color = "#af52bf";
                } else if (extensiones.pdf.includes(item.ext)) {
                    data.preview = "/img/icon-pdf.svg";
                    data.tipo = "PDF";
                    data.color = "#8bc34a";
                } else if (extensiones.excel.includes(item.ext)) {
                    data.preview = "/img/icon-excel.svg";
                    data.tipo = "Excel";
                    data.color = "#8bc34a";
                } else if (extensiones.scorm.includes(item.ext)) {
                    data.preview = "/img/icon-zip.svg";
                    data.tipo = "Scorm";
                    data.color = "#ffac33";
                }

            return data;
        },
        validatedFileExtension(file, fileType) {
            console.log(file.type)
            let extension = mime.extension(file.type);
            // Validacion para scorms
            if (file.type === 'application/x-zip-compressed')
                extension = 'zip'
            console.log('Extension :: ', extension)
            // console.log('fileType :: ', fileType)
            if (extension) {
                if (this.TypeOf(fileType) === 'array') {
                    let valid = false
                    fileType.forEach(el => {
                        if (extensiones[el].includes(extension))
                            valid = true
                    })
                    return valid
                } else if (this.TypeOf(fileType) === 'string') {
                    return extensiones[fileType].includes(extension);
                } else {
                    return false
                }
                // return extensiones[fileType].includes(extension);
            }
            return false;
        },

        addValuesFromArrayToUrl(arrayValues, nameParam = "param", propName = "id") {
            let tempString = ``;
            arrayValues.forEach((el) => {
                // if (this.TypeOf(el) === 'string' || this.TypeOf(el) === 'number') {
                if (["string", "number"].includes(this.TypeOf(el))) {
                    tempString += `&${nameParam}[]=${el}`;
                } else {
                    let key = el.code ? el.code : el.id;
                    tempString += `&${nameParam}[]=${key}`;
                }
            });
            return tempString;
        },

        addParamsToURL(mainArrayStr, filter) {
            mainArrayStr = "";
            for (const prop in filter) {
                let objValue = filter[prop];
                if (this.TypeOf(objValue) !== "undefined") {
                    if (this.TypeOf(objValue) === "array") {
                        // console.log(prop, objValue)
                        mainArrayStr += this.addValuesFromArrayToUrl(objValue, prop);
                    } else {
                        // 	if (TypeOf(objValue) === 'boolean') {
                        // 	mainArrayStr += `&${prop}=${objValue}`
                        // }	else{
                        if (objValue) mainArrayStr += `&${prop}=${objValue}`;
                    }
                }
            }
            return mainArrayStr;
        },
        refreshDefaultTable(table, filters = null, page = null) {
            // console.log('refreshDefaultTable')
            // console.log(table)
            let vue = this;
            if (vue.$refs[table.ref]) {
                table.filters = vue.addParamsToURL(table.filters, filters);
                vue.$refs[table.ref].getData(table.filters, page);
            }
        },
        advanced_filter(table, filters, page = null) {
            let vue = this;
            vue.open_advanced_filter = false;
            vue.refreshDefaultTable(table, filters, 1);
        },
        closeSimpleModal(dialog) {
            // console.log("Dialog REF ::", dialog.ref);
            dialog.open = false;
        },
        openSimpleModal(dialog) {
            // console.log("Dialog REF ::", dialog.ref);
            dialog.open = true;
        },
        async openFormModal(dialog, rowData = null, action = null, title = null) {
            let vue = this;

            if (action != 'status' && action != 'delete')
                vue.showLoader()

            dialog.action = action || (rowData ? "edit" : "create");
            dialog.title = title || (rowData ? "Editar " : "Crear ") + dialog.resource;
            dialog.open = true;

            await vue.$refs[dialog.ref].loadData(rowData);
            await vue.$refs[dialog.ref].resetValidation();

            vue.$nextTick(() => {
                vue.$refs[dialog.ref].loadSelects();
            });

            if (action != 'status' && action != 'delete')
                vue.hideLoader()
        },

        async openCRUDPage(url) {
            let vue = this;
            window.location.href = url;
            //
            // dialog.action = action || (rowData ? 'edit' : 'create')
            // dialog.title = title || (rowData ? 'Editar ' : 'Crear ') + dialog.resource
            // dialog.open = true
            //
            // await vue.$refs[dialog.ref].loadData(rowData)
            // await vue.$refs[dialog.ref].resetValidation()
            //
            // vue.$nextTick(() => {
            //     vue.$refs[dialog.ref].loadSelects()
            // })
            // this.hideLoader()
        },
        closeFormModal(dialog, table = null, filters = null) {
            let vue = this;
            // console.log("CLOSE Dialog REF ::", dialog.ref);
            // console.log(table);
            dialog.open = false;
            if (table) vue.refreshDefaultTable(table, filters);
            vue.hideLoader()
        },
        confirmDeleteAction(dialog) {
            // Llamar api delete
        },
        validateForm(refForm) {
            let vue = this;
            if (vue.$refs[refForm]) {
                // console.log('FORMULARIO VALIDADO :: ',refForm)
                return vue.$refs[refForm].validate();
            }
        },
        resetFormValidation(refForm) {
            let vue = this;
            if (vue.$refs[refForm]) {
                // console.log('FORMULARIO REINICIADO :: ',refForm)
                return vue.$refs[refForm].resetValidation();
            }
        },
        getRules(arrayRules) {
            let tempRules = [];
            arrayRules.forEach((labelRule) => {
                if (labelRule.indexOf("required") > -1) {
                    const tempRule = (v) => !!v || "Campo requerido";
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("max") > -1) {
                    let split = labelRule.split(":");
                    let max = split[1];
                    const tempRule = (v) =>
                        (v && v.length <= max) || `El campo debe tener menos de ${max} caracteres`;
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("min") > -1) {
                    let split = labelRule.split(":");
                    let min = split[1];
                    const tempRule = (v) =>
                        (v && v.length >= min) || `El campo debe tener más de ${min} caracteres`;
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("text") > -1) {
                    const tempRule = (v) => /^[a-zA-ZáéíóúñÁÉÍÓÚÑ ]+$/i.test(v) || "No se permiten números";
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("number") > -1) {
                    const tempRule = (v) => /^[0-9]+$/i.test(v) || "No se permiten letras";
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("24hr") > -1) {
                    const tempRule = (v) =>
                        /^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/i.test(v) ||
                        "El formato de la hora debe ser de 24 horas";
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("min_value") > -1) {
                    let split = labelRule.split(":");
                    let min = split[1];
                    const tempRule = (v) => (v && v >= min) || `El valor debe ser mayor a ${min}`;
                    tempRules.push(tempRule);
                }

                if (labelRule.indexOf("max_value") > -1) {
                    let split = labelRule.split(":");
                    let max = split[1];
                    const tempRule = (v) => (v && v >= max) || `El valor debe ser menor a ${max}`;
                    tempRules.push(tempRule);
                }
            });
            return tempRules;
        },
        removeFileFromDropzone(file, inputRef) {
            /*
             * Cuando abres un modal y seleccionas una imagen
             * Cierras el modal
             * El dropzone mantiene el preview de la imagen subida
             * A pesar de haber limpiado el v-model
             * -> Solución temporal:
             * -> Se llama la funcion removeAll del dropzoneDefault
             * -> para poder quitar todos los archivos
             * */
            let vue = this;
            if (vue.TypeOf(file) === "object" && vue.$refs[inputRef] && vue.$refs[inputRef].$refs.dropzoneDefault)
                vue.$refs[inputRef].$refs.dropzoneDefault.removeAll();
        },
        setFile(file, resource, resource_prop) {
            let vue = this;
            if (vue.TypeOf(file) === "object") {
                resource[`file_${resource_prop}`] = file;
            } else {
                resource[resource_prop] = file;
            }
        },
        getMultipartFormData(method, data, fields, file_fields = [], array_fields = []) {
            let vue = this;

            const formData = new FormData();
            formData.append("_method", method);

            for (let key in file_fields) {

                let item = file_fields[key];

                // file_fields.push(item);
                file_fields.push(item + '_id');
                file_fields.push("file_" + item);
            }

            // console.log(file_fields)

            for (let key in data) {
                if (fields.includes(key) || file_fields.includes(key) || array_fields.includes(key)) {
                    let file_field = "file_" + key;

                    if (file_fields.includes(file_field)) {

                        // console.log('IF include file_field ' + file_field)
                        // console.log(typeof data[file_field])
                        if (data[file_field] && typeof data[file_field] === "object") {
                            // console.log('IF include file_field ' + file_field + ' object')
                            formData.append(file_field, data[file_field]);
                        } else if (data[key] && vue.TypeOf(data[key]) === "string") {
                            // console.log('IF include file_field ' + file_field + ' string')
                            formData.append(key, data[key]);
                        }
                    } else {
                        // console.log('ELSE include file_field ' + file_field)
                        if (array_fields.includes(key)) {
                            for (let item in data[key]) {
                                if (typeof data[key][item] === "object") {
                                    for (let i in data[key][item]) {
                                        let value = data[key][item][i] == null ? "" : data[key][item][i];
                                        // console.log('object value?')
                                        // console.log(value)

                                        // value = typeof value === "object" ? Object.keys(value).map((key) => [Number(key), value[key]]) : value;
                                        formData.append(key + "[" + item + "][" + i + "]", value);
                                    }
                                } else {
                                    let value = data[key][item] == null ? "" : data[key][item];
                                    formData.append(key + "[" + item + "]", value);
                                }
                            }
                        } else {
                            if (data[key] == null) {
                                formData.append(key, "");
                            } else {
                                if (typeof data[key] === "object") {
                                    if (vue.TypeOf(data[key]) === "array") {
                                        for (let i in data[key]) {
                                            let value = data[key][i].id || data[key][i]
                                            formData.append(key + "[]", value);
                                        }
                                    } else if (vue.TypeOf(data[key]) === "object" && !formData.get(key)) {
                                        let value = data[key].id

                                        formData.append(key, value);
                                    }
                                } else {
                                    formData.append(key, data[key]);
                                }
                            }
                        }
                    }
                }
            }

            return formData;
        },
        clearObject(obj) {
            if (typeof obj === "string") {
                obj = "";
            } else if (typeof obj === "object") {
                for (let key in obj) {
                    if (!obj.hasOwnProperty(key)) continue;
                    let val = obj[key];
                    switch (typeof val) {
                        case "string":
                            // @ts-ignore
                            obj[key] = "";
                            break;
                        case "number":
                            // @ts-ignore
                            obj[key] = 0;
                            break;
                        case "boolean":
                            // @ts-ignore
                            obj[key] = false;
                            break;
                        case "object":
                            if (val === null) break;
                            if (val instanceof Array) {
                                // @ts-ignore
                                obj[key] = [];
                                break;
                            }
                            val = {};
                            break;
                    }
                }
            }
        },
        logFormDataValues(formData) {
            if (formData) {
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ' => ' + pair[1]);
                }
            }

        },
        downloadReportFromNode(url, data = null) {
            let vue = this
            vue.showLoader()
            const base_api_reportes = process.env.MIX_API_REPORTES
            const nodeAPI = base_api_reportes + url
            vue.$http.post(nodeAPI, data)
                .then((res) => {
                    if (!res.data.error) {
                        try {
                            let dateNow = res.data.createAt;
                            let extension = res.data.extension;
                            let modulo = res.data.modulo;
                            let ExcelNuevoNombre =
                                modulo + moment(res.createAt).format("L") + " " + moment(res.createAt).format("LT");
                            FileSaver.saveAs(`/storage/${dateNow + extension}`, ExcelNuevoNombre + extension);
                            this.hideLoader()
                        } catch (error) {

                            alert("Se ha encontrado el siguiente error : " + error);
                            this.hideLoader()
                            return error;
                        }
                    } else {
                        alert("Se ha encontrado el siguiente error : " + res.data.error);
                        vue.hideLoader()
                    }
                })
                .catch((error) => {
                    console.log(error);
                    console.log(error.message);
                    alert("Se ha encontrado el siguiente error : " + error);
                    vue.hideLoader()
                    return false
                });
        },
        openLink(url)
        {
            window.location.href = url
        }
    }
};
