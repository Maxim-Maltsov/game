
<template>
    
    <!-- /// Side Bar /// --> 
    <div id="side-bar" class="col-4">

        <!-- alert error -->
        <section v-if="errored" class="d-flex flex-column align-items-center">
            <div class="card-info mt-5" style="width: 80%">
                <div class="alert alert-danger mt-5" role="alert">
                    <strong>Уведомлене!</strong><div class="text-secondary">Не удалось получить данные. Попробуйте позже...</div>
                </div>  
            </div>
        </section>

        <!-- alertloading -->
        <section v-if="loading">
            <div class="card-info mt-5 d-flex flex-column align-items-center" >
                <div class="spinner-border m-3" role="status" style="width: 2rem; height: 2rem;" >
                    <span class="visually-hidden"></span>
                </div>
                <strong>Загрузка...</strong>
            </div>
        </section>

        <!-- users cards -->
        <section v-else >
            <div class="cards d-flex flex-column justify-content-center align-items-center shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                    
                <!-- cards -->
                <div  class="card-body bg-dark opacity-75" style="width: 100%; border-radius: 4px">
                    <div v-for="user of users" :key="user.id" class="card text-center mt-1">
                        <div class="card-body d-flex flex-column  align-items-center">
                        <h6 class="h6 card-title text-secondary">
                             {{ user.name }}  <small class="text-success">free</small></h6>
                        <button v-on:click="inviteToPlay(user.id)" class="btn btn-outline-success hover-shadow" type="button" style="width: 80%">Play</button>
                        </div>
                    </div>
                </div>

                <!-- pagination -->
                <nav aria-label="Page navigation example" class="mt-3">
                    <ul class="pagination pagination-lg">
                        <li  class="page-item" :class="{disabled: !Boolean(pagination.prev)}" v-on:click.prevent="getUsers(pagination.prev)">
                            <a class="page-link text-success" href="#"> &laquo;  </a>
                        </li>

                        <li class="page-item" :class="{disabled:true}">
                            <a class="page-link text-secondary" href="javascript:void(0)"> <span class="text-secondary">{{pagination.current_page}} из {{pagination.last_page}}</span> </a>
                        </li>
                    
                        <li class="page-item" :class="{disabled: !Boolean(pagination.next)}" v-on:click.prevent="getUsers(pagination.next)">
                            <a class="page-link text-success"  href="#" > &raquo; </a>
                        </li>
                    </ul>
                </nav>

            </div>
        </section>

    </div> <!-- .col-4 -->

</template>


<script>

    export default {
        
        props: {

            token: String,
            
        },

        data() {

            return {

                users: [],

                pagination: {},
                
                loading: false,
                errored: false,

                offer: false,
                response: false,
            }
        },

        methods: {

            makePagination(data) {

                let pagination = {

                    current_page: data.meta.current_page,
                    last_page: data.meta.last_page,
                    prev: data.links.prev,
                    next: data.links.next,
                }

                this.pagination = pagination;
                // console.log(this.pagination);
            },

            getUsers(page_url) {

                this.loading = true;
                
                let url = page_url || 'api/v1/users';

                let config = {

                    headers: {
                        Authorization: "Bearer " + token,
                    }
                }
                
                axios
                    .get( url , config)
                    .then( response => {
                       
                        this.users = response.data.data;
                        this.makePagination(response.data);
                    })
                    .catch( error => {
                        
                        this.errored = true;
                        console.log(error);
                    })
                    .finally(() => (this.loading = false));

            },

             inviteToPlay(id) {
                
                alert(id);
                let config = {

                    headers: {
                        Authorization: "Bearer " + token,
                    }
                }

                axios.post('api/v1/invite-to-play', {

                    player_2: id,
                    
                }, config)
                .then(function (response) {
                    console.log(response);
                })
                .catch(function (error) {
                    console.log(error);
                });
                
                this.offer = true;
            },

        },
                
        mounted() {
            
            this.getUsers('api/v1/users');

            Echo.channel('all-auth-users')
                        .listen('AmountUsersOnlineChangedEvent', (e) => {
                            console.log(e.users);
                            this.users = e.users;
                        });
        },

}
       
</script>

<style>

    
</style>