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
    <title>Cursalab</title>
  </head>
  <style>
    html, body
    {
        display:flex; 
        flex-direction:column; 
        height:100%;
    }
    footer{
        margin-top: 50px;
        background: #1a2033;
        padding: 30px 0;
        color: #fff;
        font-size: 14px;
        clear: both;
        position: relative;
    }
    .main-style{
        padding:1.8rem 1.4rem;
        width:80%;
    }
    @media (max-width: 800) { 
        /* .main-style{
            padding:27px 91px;
            border:1px solid black;
            border-radius:120px;
            width:33vw;
        } */
    }
    .contacts {
      display: inline-grid;
      justify-items: start;
      list-style: none;
      padding: 0;
    } 
    p{
        font-size:1rem;
    }
  </style>
  <body>
        <header class="masthead mb-auto mt-3 ml-3 ">
          <div class="container-fluid">
              <!--Mods logo-->
              <div id="logo">
                <a href="#">
                  <img class="img-fluid" src="img/we-connect-logo.png" data-retina="true" width="150"> 
                </a>
              </div>
          </div>
        </header>
        <main role="main" class="container justify-content-center main-style">
            <div class="text-center">
                <h2 style="font-size:1.5rem">Actualiza tu app de Universidad Corporativa</h2>
            </div>
            <div style="margin-top:2rem">
                <p>Escoge la opción que corresponda a tu teléfono para ir a la tienda de aplicaciones.</p>
            </div>
            <div>
                <h5>Android</h5>
                <p>Si tu telefono es un android corresponde descargar una aplicación desde 
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=io.cursalab.weconnect&hl=es_PE&gl=US">
                    PlayStore. 
                  </a>
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=io.cursalab.weconnect&hl=es_PE&gl=US">
                    <img style="width: 40px;" src="/img/playstore.png" class="img-thumbnail ml-1">
                  </a>
                </p>
            </div>
            <div>
                <h5>Huawei</h5>
                <p>Si tu telefono es un Huawei moderno y cuenta solo con App Gallery, actualiza tu app desde 
                  <a target="_blank" href="https://appgallery.huawei.com/#/app/C106822677">
                    App Gallery.
                  </a>
                  <a target="_blank" href="https://appgallery.huawei.com/#/app/C106822677">
                    <img style="width: 40px;" src="/img/app_galery.png" class="img-thumbnail ml-2">
                  </a>
                </p>
                <p>Si tu telefono es un Huawei y no cuenta con App Gallery, actualiza tu app desde 
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=io.cursalab.weconnect&hl=es_PE&gl=US">
                    PlayStore. 
                  </a>
                  <a target="_blank" href="https://play.google.com/store/apps/details?id=io.cursalab.weconnect&hl=es_PE&gl=US">
                    <img style="width: 40px;" src="/img/playstore.png" class="img-thumbnail ml-1">
                  </a>
            </div>
            {{-- <div>
                <p>
                    Si ninguna de estas opciones se adecua a tu teléfono móvil, contáctate con soporte al whatsapp 
                      <a target="_blank" href="https://wa.me/51960169962?text=Necesito%20ayuda%20con%20la%20actualización%20de%20mi%20aplicación.">
                        960-169-962 
                      </a>
                      <a target="_blank" href="https://wa.me/51960169962?text=Necesito%20ayuda%20con%20la%20actualización%20de%20mi%20aplicación.">
                        <img style="width: 40px;" src="/img/whatsapp.png" class="img-thumbnail ml-1"> 
                      </a> de Lunes a Viernes, de 9am a 6pm
                </p>
            </div> --}}
        </main>
      {{-- <footer id="footer" style="margin-top: 7vh;" >
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
                <img src="img/we-connect-logo.png" class="img-fluid" data-retina="true" alt="" style="filter: brightness(0) invert(1);" width="150">
              </p>
            </div>
          </div>
          <hr>
          <div class="text-center">
            <small>© Plataforma de propiedad de La Media Digital Lab.</small>
          </div>		
        </div>
      </footer> --}}
  </body>
</html>