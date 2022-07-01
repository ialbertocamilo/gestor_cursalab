import axios from "axios";

let axiosDefault = axios.create({
    baseURL: process.env.MIX_API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.token}`
    }
});
let token = document.head.querySelector('meta[name="csrf-token"]');

axiosDefault.interceptors.request.use(
    function (config) {
        // Do something before request is sent
        console.log('CALL URL API :: ', config.url)
        // this.showLoader()

        return config
    },
    function (error) {
        // Do something with request error
        // console.log('error')
        // this.showLoader()

        return Promise.reject(error);
    },
);

axiosDefault.interceptors.response.use(
    function (response) {
        // Any status code that lie within the range of 2xx cause this function to trigger
        // Do something with response data
        // response.data = {hai: 'hai'};
        // console.log(response)
        let tempObjResponse = {
            urlResponse: response.request.responseURL,
        }
        // console.table(tempObjResponse)
        // console.info('DATOS RECIBIDOS :: ', response.data.data)
        // this.hideLoader()

        return response;
    },
    function (error) {
        // Any status codes that falls outside the range of 2xx cause this function to trigger
        // Do something with response error
        console.log('hubo un error', error)
        console.log('error.response')
        console.log(error.response)
        // this.hideLoader()
        // alert('error de servidor')
        // Vue.$notification.error("hello world", { timer: 10 });
        // mixin.methods.showAlert('asd');
        switch (error.response.status) {
            case 419:
            case 401:
                window.location.href = "/";
                break;
            case 422:
                setTimeout(() => {
                    // $('#pageloader').css('display', 'none')
                    $('#pageloader').fadeOut()
                }, 800)
                return Promise.reject(error.response.data)
                break;
            default:
                setTimeout(() => {
                    // $('#pageloader').css('display', 'none')
                    $('#pageloader').fadeOut()
                }, 800)
                return Promise.reject(error)
        }
        // if (error.response.status === 419) {
        //
        // }
        //
        // return Promise.reject(error);
    },
);

axiosDefault.defaults.headers.common['X-CSRF-TOKEN'] = token.content;

export default axiosDefault;
