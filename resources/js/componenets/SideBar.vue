
<template>
    
    <!-- /// Side Bar /// --> 
    <div id="side-bar" class="col-4">

        <!-- alert error -->
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
        </section>

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
                        <li  class="page-item"  v-on:click.prevent="getUsers(pagination.prev)">
                        <a class="page-link text-success" href="#"> &laquo;  </a>
                        </li>

                        <li class="page-item" :class="{disabled:true}">
                            <a class="page-link text-secondary" href="javascript:void(0)">1 <span class="text-secondary">из </span>2 </a>
                        </li>
                    
                        <li class="page-item"  v-on:click.prevent="getUsers(pagination.next)">
                            <a class="page-link text-success"  href="#" > &raquo; </a>
                        </li>
                    </ul>
                </nav>

                <!-- <nav aria-label="Page navigation example" class="mt-3">
                    <ul class="pagination pagination-lg">
                        <li  class="page-item" :class="{disabled: !Boolean(pagination.prev)}" v-on:click.prevent="getUsers(pagination.prev)">
                            <a class="page-link text-success" href="#"> &laquo;  </a>
                        </li>

                        <li class="page-item" :class="{disabled:true}">
                            <a class="page-link text-secondary" href="javascript:void(0)"> <span class="text-secondary">@{{pagination.current_page}} из @{{pagination.last_page}}</span> </a>
                        </li>
                    
                        <li class="page-item" :class="{disabled: !Boolean(pagination.next)}" v-on:click.prevent="getUsers(pagination.next)">
                            <a class="page-link text-success"  href="#" > &raquo; </a>
                        </li>
                    </ul>
                </nav> -->

        </div>
    </section>

    </div> <!-- .col-4 -->

</template>


<script>

    export default {
        
       props: {

            users: Array
       }
    }

</script>

<style>
    
</style>