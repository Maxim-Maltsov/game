
<template>
    
    <!-- /// Side Bar /// --> 
    <div id="side-bar" class="col-4">

        <!-- alert error -->
        <section v-if="errored" class="d-flex flex-column align-items-center">
            <div class="card-info mt-5" style="width: 80%">
                <div class="alert alert-danger mt-5" role="alert">
                    <strong>Notification!</strong><div class="text-secondary">Data could not be retrieved. Try again later...</div>
                </div>  
            </div>
        </section>
        
        <!-- alert loading -->
        <section v-if="loading">
            <div class="card-info mt-5 d-flex flex-column align-items-center" >
                <div class="spinner-border m-3" role="status" style="width: 2rem; height: 2rem;" >
                    <span class="visually-hidden"></span>
                </div>
                <strong>Loading...</strong>
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
                             {{ user.name }} <small class="text-success">free</small>   </h6>
                        <button v-on:click="inviteToPlay(user.id)" class="btn btn-outline-success hover-shadow" :class="{ disabled: /*!user.can_play  || */ (auth_id == user.id) }" type="button" style="width: 80%">Play</button>
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

    <div  class="col-8 text-center flex-column justify-content-center align-items-center">
        
        <!-- offer card -->
        <section v-if="offer" class="d-flex flex-column align-items-center">
            <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                <div class="card-body bg-dark opacity-75" style="border-radius: 4px">

                    <div class="offer-card" style="width: 100%">
                        <div class="alert alert-light m-0" role="alert">
                            <h6 class="h6" ><i class="text-success"> {{ game.player_1.name }} </i> offers to play the game!</h6>
                            <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                <a href="#" class="btn btn-outline-success btn-sm hover-shadow" style="width: 25%">Ok</a>
                                <a href="#" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</a>
                            </div>
                        </div>  
                    </div> 

                </div>
            </div>
        </section>

        <!-- response card -->
         <section v-if="waiting" class="d-flex flex-column align-items-center">
            <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                <div class="card-body bg-dark opacity-75" style="border-radius: 4px">

                    <div class="offer-card" style="width: 100%">
                        <div class="alert alert-light m-0" role="alert">
                            <h6 class="h6" > Waiting for a response from <i class="text-success"> {{ game.player_2.name }}</i> ...</h6>
                            <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                <button v-on:click=" destroyGame(game.id)" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</button>
                            </div>
                        </div>  
                    </div>

                </div>
            </div>
        </section>

         <!-- alert exception -->
        <section v-if="exception" class="d-flex flex-column align-items-center">
            <div class="card-info shadow" style="width: 100%">
                <div class="alert alert-warning mb-0 p-y-1" role="alert">
                    <div class="text-secondary"> {{ message }} </div>
                </div>  
            </div>
        </section>

        
        
        <section v-show="play" id="playing-field">

            <!-- alert error -->
            <section v-if="errored" class="d-flex flex-column align-items-center">
                <div class="card-info mt-5" style="width: 80%">
                    <div class="alert alert-danger mt-5" role="alert">
                        <strong>Notification!</strong><div class="text-secondary">Data could not be retrieved. Try again later...</div>
                    </div>  
                </div>
            </section>
            
            <!-- alert loading -->
            <section v-if="loading">
                <div class="card-info mt-5 d-flex flex-column align-items-center" >
                    <div class="spinner-border m-3" role="status" style="width: 2rem; height: 2rem;" >
                        <span class="visually-hidden"></span>
                    </div>
                    <strong>Loading...</strong>
                </div>
            </section>

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

        </section> <!-- playing_field -->
        

    </div> <!-- .col-8 -->

</template>


<script>

    export default {
        
        props: {

            token: String,

            auth_id: String,
            
        },

        data() {

            return {

                users: [],

                game: {},

                player_1: {},
                player_2: {},

                pagination: {},
                
                loading: false,
                errored: false,

                offer:    false,
                waiting: false,
                play:     false,

                
                message: '',
                exception: false,
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
                        Authorization: "Bearer " + this.token,
                    }
                }
                
                axios
                    .get( url , config)
                    .then( response => {
                       
                        this.users = response.data.data;
                        this.makePagination(response.data);
                    })
                    .catch( error => {
                        
                        console.log(error);
                    })
                    .finally(() => (this.loading = false));

            },

             inviteToPlay(id) {
                
                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post('api/v1/invite-to-play', {

                   player_2: id,
                    
                }, config)
                .then( response => {

                    this.exception = (response.data.data.exception)? response.data.data.exception : false;

                    if (this.exception) {

                        this.message = response.data.data.message;
                        // console.log(this.message);
                        // console.log(this.exception);
                    }
                    else {

                        this.game = response.data.data;
                        // this.player_1 = response.data.data.player_1
                        // this.player_2 = response.data.data.player_2

                        this.waiting = true;

                        // console.log(this.exception);
                        console.log(this.game);
                        // console.log(this.player_1.name);
                        // console.log(this.player_2.name);
                    }
                    
                })
                .catch( error => {

                    console.log(error);
                });
            },

            destroyGame(id) {

                if (!confirm('Are you sure you want to cancel the game?')) {

                    return false;
                }

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post(`api/v1/games/${id}`, {

                  _method: 'DELETE'
                    
                }, config)
                .then( response => {
                    
                    // Обнуляем данные игры у первого игрока, скрытие карточки ожидания и предупреждений.
                    this.game = {};
                    this.waiting = false;
                    this.exception = false;
                })
                .catch( error => {

                    console.log(error);
                });
            },

        },
                
        mounted() {
            
            this.getUsers('api/v1/users');

            Echo.channel('allAuthUsers')
                .listen('AmountUsersOnlineChangedEvent', (e) => {
                    
                    this.users = e.users;
                    // console.log(e.users);
                });
                                          
            Echo.private(`privateChannelFor.${this.auth_id}`)
                .listen('InviteToPlayEvent', (e) => {
                    
                    this.game = e.game;
                    // this.player_1 = e.game.player_1;
                    // this.player_2 = e.game.player_2;

                    this.offer = true;

                    // console.log( e.game.player_1.name);
                    // console.log( e.game.player_2.name);
                    // console.log( e.game);
                });



        },

}
       
</script>

<style>

     .hover-shadow:hover {
        
        box-shadow:0 .5rem 1rem rgba(0,0,0,.15);
    }

    
</style>