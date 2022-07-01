<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css?family=Montserrat+Alternates:300,400,400i,500,500i,600,700|Roboto:300,400,500&amp;display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <title>Universidad Corporativa</title>
  </head>
  <style>
    .contacts {
      display: inline-grid;
      justify-items: start;
      list-style: none;
      padding: 0;
    } 
    footer{
      margin-top: 50px;
        background: #1a2033;
        padding: 30px 0;
        color: #fff;
        font-size: 14px;
    }
    .credit-contents{
      width: 35%;
    float: none;
    margin: auto;
    }
    #frm-uc input, #frm-uc textarea {
    border: 0;
    background-color: #8483831f !important;
    border-radius: 10px;
    outline: none;
    width: 100%;
    padding: 15px 50px;
    font-size: 18px;
    }
    .resumen_cursos{
      color: #333333;
      text-align: center;
      font-weight: bold;
      margin-bottom: 20px;
    }
    .content_progreso {
      margin: 15px 35px;
    }
    .fade-enter-active, .fade-leave-active {
      transition: opacity .5s
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
      opacity: 0
    }
  </style>
  <body>
        <header class="masthead mb-auto mt-3 ml-3 ">
          <div class="container-fluid">
              <!--Mods logo-->
              <div id="logo">
                <a href="#">
                  <!-- <img class="img-fluid" src="https://gestor.universidadcorporativafp.com.pe/images/1867057438.png" data-retina="true"> -->
                  <img class="img-fluid" src="img/logo.png" data-retina="true" width="150"> 
                </a>
              </div>
          </div>
        </header>

        <main role="main" class="container justify-content-center"id="subirarchivo" >
          <div class="row content_progreso justify-content-center">
            <div class="col-lg-7 pt-5">
                <h3 class="resumen_cursos">Registrar archivo</h3>
                <h6>Permitirá subir un archivo desde tu cámara o alguna carpeta</h6>
            </div>
            <transition name="fade">
              <div class="col-12 mt-5" v-if="success" >
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <div>
                    <h4 class="alert-heading">¡Bien hecho!</h4> 
                    <button type="button" class="close" @click="success = !success">
                      <span aria-hidden="true">&times;</span>
                    </button>

                  </div>
                  <p>Tu archivo se ha subido correctamente.</p>
                </div>
              </div>
            </transition>
            <div class="mt-5">
                <form @submit.prevent="cargaArchivo" method="post" id="frm-uc" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                              <label> Adjuntar Archivo</label>
                              <div class="custom-file">
                                <input id="up-file" name="file" type="file" placeholder="http://drive/carpeta/tu-archivo.pdf" 
                                @change="onFileSelected" accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
                                <small>(Imagen o PDF.)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label>Descripción (Obligatorio)</label>
                                <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" v-model="descripcion" placeholder="Ingresa aquí la tarea que estas cargando"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-md-12 mb-3 text-center">
                        <button  v-if="!loading" @click="cargaArchivo" :disabled="buttonForm" type="button" class="btn btn-primary" style="background: #22b573 !important">Subir archivos</button>
                        <button v-if="loading" class="btn btn_action" type="button" style="background: #22b573 !important">
                          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" disabled="loading"></span>
                          Subiendo archivos..
                      </button>
                      </div>
                  </div>
                </form>
              </div>
          </div>
        </main>


        


      <footer id="footer" style="margin-top: 18vh;" >
        <div class="container-fluid">
          <div class="sub_footer row">
            <div class="copyright col-lg-6 col-md-6 mr-lg-auto text-center ">
              <h5 class="mt-0">Contáctanos</h5>
              <ul class="contacts">
                <li class="primera_p"><span>
                  <a style="text-decoration: none; color:white;" href="tel:51960169962"><i class="fas fa-mobile-alt"></i> +51 960 169 962 (Lunes a Viernes, de 9am a 6pm)</a>
                </span>
                </li>
                <li class="segunda_p"><span>
                  <a  style="text-decoration: none; color:white;" href="mailto:soporte@lamediadl.com?Subject=Asistencia"><i class="fa fa-envelope"></i> soporte@lamediadl.com</a>
                </span></li>
              </ul>
            </div>
    
            <div class="col-lg-4 col-md-6 ml-lg-auto d-none">
            </div>
            <div class="credits col-lg-5 col-md-12 text-center">
              <p class=" credits_content">
                <img src="img/logo.png" class="img-fluid" data-retina="true" alt="" style="filter: brightness(0) invert(1);" width="150">
              </p>
            </div>
          </div>
          <!--/row-->	
          <hr>
          <div class="text-center">
            <small>© Plataforma de propiedad de La Media Digital Lab.</small>
            
          </div>		
        </div>
      </footer>

    <!-- Optional JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <!-- production version, optimized for size and speed -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
    <!-- development version, includes helpful console warnings -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.0/axios.min.js" integrity="sha512-DZqqY3PiOvTP9HkjIWgjO6ouCbq+dxqWoJZ/Q+zPYNHmlnI2dQnbJ5bxAHpAMw+LXRm4D72EIRXzvcHQtE8/VQ==" crossorigin="anonymous"></script>
    <script>
      var subirarchivo = new Vue({
        el: '#subirarchivo',
        data: {
                usu_id: 1,
                file : '',       
                descripcion: "", 
                buttonForm: true,
                loading : false,
                success: false
        },
        methods: {
          onFileSelected(event){
              this.file = event.target.files[0];
              // console.log(this.file);
          },
          cargaArchivo(){
                    this.loading = true;
                    const fd = new FormData();
                    fd.append('usuario_id',this.usu_id);
                    fd.append('file',this.file, this.file.name);
                    fd.append('description',this.descripcion);
    
                    axios.post('{{Request::getSchemeAndHttpHost() }}/api/rest/usuario_upload_file',fd,  {
                        headers: {
                          Authorization: `Bearer {{$_GET['token']}}` //the token is a variable which holds the token
                        }
                      })
                    .then(response => {
                        var res = response.data

                        // console.log('Respuesta ::',response);
                        if (!res.error) {
                            // $("#successMsg").modal('show')
                            // setTimeout(() => {
                            //     $("#successMsg").modal('hide')
                            // }, 3000);
                            this.url = ""
                            this.descripcion = ""
                            this.loading = false;
                            this.success = true;
                            document.getElementById('up-file').value = '';
                        }
                    })
                    .catch(e => {
                        console.log('Error :: ',e)
                        this.loading = false;
                    })
          }                       
        },
        watch: {
                descripcion : function(value){
                    this.buttonForm = this.file && this.descripcion.length > 4 ? false : true;
                },
                file : function(value){
                    this.buttonForm = this.file && this.descripcion.length > 4 ? false : true;
                }
            },
      })
    </script>
  </body>
</html>