@extends('layouts.my-app')


@section('content')

    <div class="row mt-5 justify-content-between align-items-center gx-5">

        <!-- Playing field -->
        <div class="col-8 text-center flex-column justify-content-center align-items-center">

             <!-- cards -->
             <div class="card m-2" style="width: 100%">
                <div class="card-body bg-secondary" style="border-radius: 4px">

                    <div class="card text-center mt-1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <h6 class="h6 card-title text-secondary">Raund Time: <span class="text-success">30</span> sec. </h6>
                        </div>
                    </div>

                        </div>
                    </div>

                    <div class="card text-center mt-1">
                        <div class="card-body d-flex flex-column justify-content-start align-items-start">
                            <h6 class="h6 card-title text-secondary p-3">Raund Results</h6>
                            <h6 class="h6 card-title text-secondary">Player One : <span class="text-success">Rock</span></h6>
                            <h6 class="h6 card-title text-secondary">Player Two: <span class="text-success">Paper</span></h6>
                            <h6 class="h6 card-title text-secondary">Winner: <span class="text-success">Player One</span></h6>
                        </div>
                    </div>
                    
                </div>
            </div>
    
        </div>

        <!-- Side bar-->
        <div class="col-4">

            <div class="cards" style="width: 100%">
                <div class="card-body bg-secondary" style="border-radius: 4px">
                  
                    <div class="card text-center mt-1">
                        <div class="card-body d-flex flex-column justify-content-between align-items-center">
                        <h6 class="h6 card-title text-secondary">Name: <small class="text-success">free</small></h6>
                        <a href="#" class="btn btn-outline-success" style="width: 80%">Play</a>
                        </div>
                    </div>

                    <div class="card text-center mt-1">
                        <div class="card-body d-flex flex-column justify-content-between align-items-center">
                        <h6 class="h6 card-title text-secondary">Name: <small class="text-danger">is busy</small></h6>
                        <a href="#" class="btn btn-outline-success" style="width: 80%">Play</a>
                        </div>
                    </div>

                    <div class="card text-center mt-1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <h6 class="h6 card-title text-secondary">Name: <small class="text-danger">is busy</small></h6>
                        <a href="#" class="btn btn-outline-success">Play</a>
                        </div>
                    </div>

                    <div class="card text-center mt-1">
                        <div class="card-body d-flex justify-content-between align-items-center">
                        <h6 class="h6 card-title text-secondary">Name:  <small class="text-success">free</small></h6>
                        <a href="#" class="btn btn-outline-success">Play</a>
                        </div>
                    </div>

                </div>
              </div>

              
        </div> <!-- .col-4 -->

        {{-- <section v-if="errored" class="d-flex flex-column align-items-center">
            <div class="card-info mt-5" style="width: 70%">
                <div class="alert alert-danger mt-5" role="alert">
                    <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Не удалось получить данные.') }}</div>
                </div>  
            </div>
        </section>

        <section v-if="loading" class="d-flex flex-column align-items-center">
            <div class="card-info mt-5" style="width: 70%">
                <div class="alert alert-warning mt-5" role="alert">
                    <strong>{{ __('Уведомлене! ') }}</strong><div class="text-secondary">{{ __('Данные загружаются...') }}</div>
                </div>
                <div class="spinner-border text-warning m-5" role="status">
                    <span class="sr-only"></span>
                </div>  
            </div>
        </section>

        <section v-else class="text-center d-flex flex-column justify-content-center align-items-center mt-5">
            
            <!-- pagination -->
            <nav aria-label="Page navigation example" class="m-5">
                <ul class="pagination pagination-lg">
                    <li  class="page-item" :class="{disabled: !Boolean(pagination.prev)}" v-on:click.prevent="getEvents(pagination.prev)">
                      <a class="page-link" href="#"> &laquo;  </a>
                    </li>

                    <li class="page-item" :class="{disabled:true}">
                        <a class="page-link" href="javascript:void(0)"> <span class="text-secondary">@{{pagination.current_page}} из @{{pagination.last_page}}</span> </a>
                    </li>
                
                    <li class="page-item" :class="{disabled: !Boolean(pagination.next)}" v-on:click.prevent="getEvents(pagination.next)">
                        <a class="page-link"  href="#" > &raquo; </a>
                    </li>
                </ul>
            </nav>

            <!-- cards -->
            <div v-for="event of events" :key="event.id" class="card m-2" style="width: 70%">
                <div class="card-body">
                    <h5 class="h5 card-title text-success"> @{{ event.title }} </h5>
                    <p class="card-text text-secondary"> @{{ event.description }} </p>
                </div>
            </div>

            
        </section> --}}

    </div>
    
    {{-- <script> var token = "{{ $token }}"; </script> --}}

@endsection