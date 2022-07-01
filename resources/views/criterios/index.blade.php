@extends('layouts.appback')

@section('content')
  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            {{-- <div class="pr-4">
                <h2 class="no-margin-bottom">Criterios</h2>
            </div> --}}
            {{-- <div class="ml-auto">
                <form id="searchForm" action="" role="search" >
                  <div class="input-group">
                  <input type="search" name='q' class="form-control" placeholder="Palabra clave" aria-label="Palabra clave" aria-describedby="basic-addon2">
                  <div class="input-group-append">
                    <button class="btn btn-secondary" type="submit"><i class="fa fa-search"></i></button>
                  </div>
                </div>
                </form>
            </div> --}}
        </div>
    </div>
  </header>
  
  <v-app>
    <section style="padding: 20px 0 0 20px;" >   
        <criterios-layout/>
    </section>
  </v-app>
@endsection
