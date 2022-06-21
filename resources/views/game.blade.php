@extends('layouts.my-app')


@section('content')

    <div class="row mt-3 justify-content-between align-items-start gx-5">
        
        <!-- /// Offer Card /// -->
        <div v-if="offer" id="offer-block" class="col-8 text-center flex-column justify-content-center align-items-center">
            <section class="d-flex flex-column align-items-center">
                <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                    <div class="card-body bg-dark opacity-75" style="border-radius: 4px">

                        <div class="offer-card" style="width: 100%">
                            <div class="alert alert-light m-0" role="alert">
                                <h6 class="h6" ><i class="text-success"> Player one</i> offers to play the game!</h6>
                                <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                    <a href="#" class="btn btn-outline-success btn-sm hover-shadow" style="width: 25%">Ok</a>
                                    <a href="#" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</a>
                                </div>
                            </div>  
                        </div>

                    </div>
                </div>
            </section>
        </div>


        <!-- /// Playing Field /// -->
        <div v-if="playing" id="playing-field" class="col-8 text-center flex-column justify-content-center align-items-center">

             {{-- <!-- alert error -->
             <section v-if="errored" class="d-flex flex-column align-items-center">
                <div class="card-info mt-5" style="width: 80%">
                    <div class="alert alert-danger mt-5" role="alert">
                        <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Не удалось получить данные.') }}</div>
                    </div>  
                </div>
            </section>
            
            <!-- alert loading -->
            <section v-if="loading">
                <div class="card-info mt-5 d-flex flex-column align-items-center" >
                    <div class="alert alert-warning mt-5" role="alert" style="width: 80%">
                        <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Данные загружаются...') }}</div>
                    </div>
                    <div class="spinner-border m-3" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </section> --}}

            <!-- playing field cards -->
            <section v-else >
                <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                    <div class="card-body bg-dark opacity-75" style="border-radius: 4px">
    
                        <!-- timer -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex justify-content-between align-items-center">
                            <h6 class="h6 card-title text-secondary"><i class="text-success"> Round Time:</i> 30 sec. </h6>
                            </div>
                        </div>
    
                        <!-- figures -->
                        <div class="card text-center mt-1">
                            <div class="btn-group p-3" role="group" aria-label="Figures" style="width: 100%">
                                <a href="#" class="btn btn-outline-success hover-shadow" style="width: 50%">Rock</a>
                                <a href="#" class="btn btn-outline-success hover-shadow" style="width: 50%">Scissors</a>
                                <a href="#" class="btn btn-outline-success hover-shadow" style="width: 50%">Paper</a>
                                <a href="#" class="btn btn-outline-success hover-shadow" style="width: 50%">Lizard</a>
                                <a href="#" class="btn btn-outline-success hover-shadow" style="width: 50%">Spoke</a>
                            </div>
                        </div>
    
                        <!-- round results -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start">
                                <h6 class="h6 card-title text-success py-3"><i>Round Results</i></h6>
                                <h6 class="h6 card-title text-secondary">Player One : <span class="text-success">Rock</span></h6>
                                <h6 class="h6 card-title text-secondary">Player Two: <span class="text-success">Paper</span></h6>
                                <h6 class="h6 card-title text-secondary">Winner: <span class="text-success">Player One</span></h6>
                            </div>
                        </div>
                        
                         <!-- score users -->
                         <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start">
                                <h6 class="h6 card-title text-success py-3"><i>Score</i></h6>
                                <h6 class="h6 card-title text-secondary">Player One : <span class="text-success">0</span></h6>
                                <h6 class="h6 card-title text-secondary">Player Two: <span class="text-success">1</span></h6>
                            </div>
                        </div>
    
                        <!-- game history -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start">
                                <h6 class="h6 card-title text-success pt-3 pb-1"><i>Game History </i></h6>
                                <div class=" my-0 py-0 pt-2 d-flex flex-column justify-content-start align-items-start">
                                    <h6 class="h6 card-title text-success">Round : <span class="text-secondary">1</span></h6>
                                    <h6 class="h6 card-title text-secondary">Player One : <span class="text-success">Rock</span></h6>
                                    <h6 class="h6 card-title text-secondary">Player Two: <span class="text-success">Paper</span></h6>
                                    <h6 class="h6 card-title text-secondary">Winner: <span class="text-success">Player One</span></h6>
                                </div>
                            </div>
                        </div>
    
                    </div>
                </div>
            </section>
    
        </div> <!-- .col-8 -->

        <!-- /// Side Bar /// --> 
        <div id="side-bar" class="col-4">

            {{-- <!-- alert error -->
            <section v-if="errored" class="d-flex flex-column align-items-center">
                <div class="card-info mt-5" style="width: 80%">
                    <div class="alert alert-danger mt-5" role="alert">
                        <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Не удалось получить данные.') }}</div>
                    </div>  
                </div>
            </section>
            
            <!-- alertloading -->
            <section v-if="loading">
                <div class="card-info mt-5 d-flex flex-column align-items-center" >
                    <div class="alert alert-warning mt-5" role="alert" style="width: 80%">
                        <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Данные загружаются...') }}</div>
                    </div>
                    <div class="spinner-border m-3" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                      
                </div>
            </section> --}}

            <!-- users cards -->
            <section v-else >
                <div class="cards d-flex flex-column justify-content-center align-items-center shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                     
                    <!-- cards -->
                    <div class="card-body bg-dark opacity-75" style="width: 100%; border-radius: 4px">
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column  align-items-center">
                            <h6 class="h6 card-title text-secondary">Name: <small class="text-success">free</small></h6>
                            <a href="#" class="btn btn-outline-success hover-shadow" style="width: 80%">Play</a>
                            </div>
                        </div>
                    </div>

                    <!-- pagination -->
                    <nav aria-label="Page navigation example" class="mt-3">
                        <ul class="pagination pagination-lg">
                            <li  class="page-item" :class="" v-on:click.prevent="getEvents(pagination.prev)">
                            <a class="page-link text-success" href="#"> &laquo;  </a>
                            </li>

                            <li class="page-item" :class="{disabled:true}">
                                <a class="page-link text-secondary" href="javascript:void(0)">1 <span class="text-secondary">из </span>2 </a>
                            </li>
                        
                            <li class="page-item" :class="" v-on:click.prevent="getEvents(pagination.next)">
                                <a class="page-link text-success"  href="#" > &raquo; </a>
                            </li>
                        </ul>
                    </nav>

                    {{-- <nav aria-label="Page navigation example" class="mt-3">
                        <ul class="pagination pagination-lg">
                            <li  class="page-item" :class="{disabled: !Boolean(pagination.prev)}" v-on:click.prevent="getEvents(pagination.prev)">
                              <a class="page-link text-success" href="#"> &laquo;  </a>
                            </li>
        
                            <li class="page-item" :class="{disabled:true}">
                                <a class="page-link text-secondary" href="javascript:void(0)"> <span class="text-secondary">@{{pagination.current_page}} из @{{pagination.last_page}}</span> </a>
                            </li>
                        
                            <li class="page-item" :class="{disabled: !Boolean(pagination.next)}" v-on:click.prevent="getEvents(pagination.next)">
                                <a class="page-link text-success"  href="#" > &raquo; </a>
                            </li>
                        </ul>
                    </nav> --}}

                </div>
            </section>
   
        </div> <!-- .col-4 -->
    </div> <!-- .row -->
    
    {{-- <script> var token = "{{ $token }}"; </script> --}}

@endsection

<!-- Playing Field -->
<style>

    .hover-shadow:hover {
        
        box-shadow:0 .5rem 1rem rgba(0,0,0,.15);
    }


</style>