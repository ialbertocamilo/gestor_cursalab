import mixin from "../mixin";

export default {
    data() {
        return {}
    },
    methods: {
        listModulos() {
            return new Promise(async (resolve, reject) => {
                let vue = this
                let url = `/common/get-modulos`
                const data = await vue.$http.get(url)
                resolve(data.data.data)
            })
        },
        listEscuelasxModulo(modulo_id, not_ids = []) {
            return new Promise(async (resolve, reject) => {
                let vue = this
                if (!modulo_id) resolve([])
                let url = `/common/get-escuelas-by-modulo/${modulo_id}?`

                if (not_ids.length > 0) {
                    not_ids.forEach((id) => url += `not[]=${id}`)
                }

                const data = await vue.$http.get(url)
                resolve(data.data.data)
            })
        },
        getCarrerasByModulo(modulo_id) {
            return new Promise(async (resolve, reject) => {
                let vue = this
                if (!modulo_id) resolve([])
                let url = `/common/get-carreras-by-modulo/${modulo_id}`
                const data = await vue.$http.get(url)
                resolve(data.data.data)
            })
        },
        getCiclosByCarrera(ciclo_id) {
            return new Promise(async (resolve, reject) => {
                let vue = this
                if (!ciclo_id) resolve([])
                let url = `/common/get-ciclos-by-carrera/${ciclo_id}`
                const data = await vue.$http.get(url)
                resolve(data.data.data)
            })
        },
        getAreasByModulo(modulo_id, params) {
            return new Promise(async (resolve, reject) => {
                let vue = this
                if (!modulo_id) resolve([])
                let url = `/common/get-areas-by-modulo/${modulo_id}?`
                url = url + this.addParamsToURL(url, params)

                const data = await vue.$http.get(url)
                resolve(data.data.data)
            })
        }
    }
}
