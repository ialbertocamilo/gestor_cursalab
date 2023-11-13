<template>
    <DefaultAlertDialog :options="options"
                         @onCancel="onCancel"
                         @onConfirm="onConfirm"
    >
            <template v-slot:content> 
                <div class="qrcode-container">
                    <canvas id="qrcode"></canvas>
                    <img src="" id="img-qr" alt="">
                    <!-- <div class="qrcode" id="qrcode"></div> -->
                    <!-- <img class="logo" :src="logoUrl" alt="Logo" /> -->
                    <button @click="descargarQR">Descargar QR</button>
                </div>
            </template>
    </DefaultAlertDialog>
</template>
<script>
import QRCode from "qrcode";
import html2canvas from "html2canvas";

export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            url: null
        }
    },
    mounted() {
       
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation(){

        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm')
        },
        loadData(url) {
            let vue = this
            vue.url=url;
            vue.generateQr();
            // QRCode.toCanvas(qrcodeContainer, resource.url, function (error) {
            // if (error) console.error(error);
            //     console.log("Código QR generado");
            // });
        },
        generateQr(){
            let vue = this;
            vue.showLoader();
            setTimeout(() => {
                // console.log(qrcodeContainer,'canvas');
                // // Crea el código QR
                // QRCode.toCanvas(document.getElementById('qrcode'), 'sample text', function (error) {
                //     if (error) console.error(error)
                //     console.log('success!');
                // })
                const opts = {
                    errorCorrectionLevel: 'H',
                    type: 'image/png',
                    quality: 1,
                    // margin: 1,
                    width : '90',
                    color: {
                        dark:"#000000",
                        light:"#ffffff"
                    }
                }

                // QRCode.toDataURL(this.url, opts, function (err, url) {
                //     if (err) throw err

                //     const img = document.getElementById('img-qr')
                //     img.src = url
                // })
                let  canvas = document.getElementById("qrcode");
                QRCode.toCanvas(canvas, this.url, opts, function (err) {
                    if (err) throw err
                    // Crea un nuevo contexto de lienzo
                    const ctx = canvas.getContext('2d')
                    // Crea un fondo en el centro (por ejemplo, un cuadrado blanco)
                    const fondoColor = '#FFFFFF'
                    const fondoSize = 40
                    const centerX = canvas.width / 2
                    const centerY = canvas.height / 2
                    ctx.fillStyle = fondoColor
                    ctx.fillRect(centerX - fondoSize / 2, centerY - fondoSize / 2, fondoSize, fondoSize)
                    // Agrega el código QR en la parte superior
                    const qrImage = new Image()
                    qrImage.src = canvas.toDataURL('image/png')
                    qrImage.onload = function () {
                        ctx.drawImage(qrImage, 0, 0, canvas.width, canvas.height)
                    }
                })
                vue.hideLoader();
            }, 2000);
        },
        descargarQR() {
            // Captura el contenido del contenedor y conviértelo en una imagen
            const qrcodeContainer = document.getElementById("qrcode");
            html2canvas(qrcodeContainer).then((canvas) => {
                const dataUrl = canvas.toDataURL("image/png");
                // Crea un enlace para la descarga de la imagen
                const enlace = document.createElement("a");
                enlace.href = dataUrl;
                enlace.download = "codigo-qr.png";
                enlace.click();
            });
        },
        loadSelects() {

        }
    }
}
</script>
<style scoped>
.qrcode-container {
  position: relative;
  display: inline-block;
}

.qrcode {
  /* Estilos para el contenedor del código QR */
}

.logo {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  /* Estilos para el logo, centrado en el contenedor */
}
</style>