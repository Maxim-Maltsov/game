
<template>
    
    <!-- /// Side Bar /// --> 
    <div id="side-bar" class="col-4 position-relative">

        <!-- alert error -->
        <section v-if="errored_users" class="d-flex flex-column align-items-center">
            <div class="card-info" style="width: 80%">
                <div class="alert  text-center" role="alert">
                    <strong class="text-secondary">Error!</strong><div class="text-secondary">Data could not be retrieved. Try again later...</div>
                </div>  
            </div>
        </section>
        
        <!-- alert loading -->
        <section v-if="loading_users">
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
                        <button v-on:click="inviteToPlay(user.id)" class="btn btn-outline-success hover-shadow" :class="{ disabled: !user.can_play || (auth_id == user.id) }" type="button" style="width: 80%">Play</button>
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
            
            <div v-if="leave" class="card d-flex flex-column justify-content-center align-items-center shadow mb-5 bg-body rounded" style="width: 100%">
                <div class="btn-group p-3" role="group" aria-label="Figures" style="width: 100%;">
                    <button v-on:click="leaveGame(game)" class="btn btn-outline-danger hover-shadow" style="width: 50%;">Leave Game</button>
                </div>
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
                            <h6 class="h6" ><i class="text-success"> {{  game.player_1.name  }} </i> offers to play the game!</h6>
                            <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                <button v-on:click="acceptInvite(game)" class="btn btn-outline-success btn-sm hover-shadow" style="width: 25%">Ok</button>
                                <button v-on:click="rejectInvite(game)" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</button>
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
                            <h6 class="h6" > Waiting for a response from <i class="text-success"> {{  game.player_2.name }} </i> ...</h6>
                            <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                <button v-on:click="cancelInvite(game)" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</button>
                            </div>
                        </div>  
                    </div>

                </div>
            </div>
        </section>

        <!-- alert exception -->
        <section v-if="exception" class="d-flex flex-column align-items-center">
            <div class="card-exception shadow" style="width: 100%">
                <div class="alert alert-warning mb-0 p-y-1" role="alert">
                    <div class="text-secondary"> {{ message }} </div>
                </div>  
            </div>
        </section>

         <!-- alert info -->
        <section v-if="info" class="d-flex flex-column align-items-center">
            <div class="card-info shadow" style="width: 100%">
                <div class="alert alert-success mb-0 p-y-1" role="alert">
                    <div class="text-secondary"> {{ message }} </div>
                </div>  
            </div>
        </section>
        
        <section v-show="play" id="playing-field">

            <!-- alert error -->
            <section v-if="errored" class="d-flex flex-column align-items-center">
                <div class="card-info" style="width: 80%">
                    <div class="alert  text-center" role="alert">
                        <strong class="text-secondary">Error!</strong><div class="text-secondary">Data could not be retrieved. Try again later...</div>
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

            <!--playing field cards -->
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
                                <button class="btn btn-outline-success hover-shadow" style="width: 50%">Rock</button>
                                <button class="btn btn-outline-success hover-shadow" style="width: 50%">Scissors</button>
                                <button class="btn btn-outline-success hover-shadow" style="width: 50%">Paper</button>
                                <button class="btn btn-outline-success hover-shadow" style="width: 50%">Lizard</button>
                                <button class="btn btn-outline-success hover-shadow" style="width: 50%">Spoke</button>
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

                pagination: {},
                
                loading: false,
                errored: false,

                loading_users: false,
                errored_users: false,

                offer:    false,
                waiting:  false,
                play:     false,
                leave:    false,

                message: '',
                exception: false,
                info: false,

    
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

                this.loading_users = true;
                
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

                         if (this.errored_users) {
                             this.errored_users = false;
                        }
                    })
                    .catch( error => {
                        
                        this.errored_users = true;
                        console.log(error);
                    })
                    .finally(() => { 
                        
                        this.loading_users = false;
                    });

            },

            initGame() {

                this.loading = true;

                let config = {

                    headers: {
                        
                        Authorization: "Bearer " + this.token,
                    }
                }
                
                axios
                    .get( 'api/v1/init-game', config)
                    .then( response => {

                        if (response.data.data.exception) {

                            console.log(response.data.data.message);
                            // this.message = response.data.data.message;
                            // this.exception = true;
                        }
                        else {

                            this.game = response.data.data.game;
                            this.waiting = response.data.data.waiting;
                            this.offer = response.data.data.offer;
                            this.play = response.data.data.play;
                            this.leave = response.data.data.leave;
                            // console.log(response.data.data.game);
                        }
                    })
                    .catch( error => {
                        
                        console.log(error);
                    })
                    .finally(() => this.loading = false );

            },

            inviteToPlay(id) {
                
                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post('api/v1/invite-play', {

                   player_2: id,
                    
                }, config)
                .then( response => {

                    
                    // this.exception = (response.data.data.exception)? response.data.data.exception : false;
                    this.info = false;

                    if (response.data.data.exception) {

                        this.message = response.data.data.message;
                        this.exception = true;
                        // console.log(this.message);
                    }
                    else {

                        this.game = response.data.data;
                        this.waiting = true;
                        
                        // console.log(this.game);
                    }
                    
                })
                .catch( error => {

                    console.log(error);
                });
            },


            acceptInvite(game) {

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }
                // `api/v1/games/${id}` // По этому маршруту работает стандартный метод update
                axios.post(`api/v1/accept-invite/${game.id}`, {

                    _method: 'PUT',
                   
                }, config)
                .then( response => {

                    this.game = response.data.data;
                    this.exception = false;
                    this.offer = false;
                    this.play = true;
                    this.leave = true;
                })
                .catch( error => {

                    console.log(error);
                });
            },

            
            cancelInvite(game) {
                
                if (!confirm('Are you sure you want to cancel the game?')) {

                    return false;
                }

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post(`api/v1/cancel-invite/${game.id}`, {

                  _method: 'DELETE'
                    
                }, config)
                .then( response => {

                    this.message = 'You canceled invite the game';
                    this.info = true;

                    this.waiting = false;
                    this.exception = false;
                })
                .catch( error => {

                    console.log(error);
                });
            },

            rejectInvite(game) {

                if (!confirm('Are you sure you want to cancel the game?')) {

                    return false;
                }

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post(`api/v1/reject-invite/${game.id}`, {

                  _method: 'DELETE'
                    
                }, config)
                .then( response => {

                    this.message = 'You rejected invite in the game.';
                    this.info = true;

                    this.offer = false;
                    this.exception = false;
                })
                .catch( error => {

                    console.log(error);
                });
            },

            leaveGame(game) {

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post(`api/v1/leave-game/${game.id}`, {

                  _method: 'DELETE'
                    
                }, config)
                .then( response => {

                    this.message = 'You leaved the game!';
                })
                .catch( error => {

                    console.log(error);
                });
            },

            
            
            // deleteGame(id) {

            //     if (!confirm('Are you sure you want to cancel the game?')) {

            //         return false;
            //     }

            //     let config = {

            //         headers: {
            //             Authorization: "Bearer " + this.token,
            //         }
            //     }

            //     axios.post(`api/v1/games/${id}`, {

            //       _method: 'DELETE'
                    
            //     }, config)
            //     .catch( error => {

            //         console.log(error);
            //     });
            // },

        },
                
        mounted() {
            
            this.getUsers();
            this.initGame();

            Echo.channel('allAuthUsers')
                .listen('AmountUsersOnlineChangedEvent', (e) => {
                  
                    this.users = e.users;
                    // console.log(e.users);
                });
                                          
            Echo.private(`privateChannelFor.${this.auth_id}`)
                .listen('InviteToPlayEvent', (e) => {
                    
                    this.game = e.game;
                    this.offer = true;
                    this.info = false;

                    
                    // console.log( e.game);
                })
                .listen('FirstPlayerCancelInviteEvent', (e) => {
                    
                    // alert("Первый игрок отменил приглашение в игру!");
                    this.message = e.message;
                    this.info = true;

                    this.offer = false;
                    this.exception = false;
                    this.game = {};
                })
                .listen('SecondPlayerRejectInviteEvent', (e) => {
                    
                    // alert("Второй игрок отказался от приглашения в игру!");
                    this.message = e.message;
                    this.info = true;

                    this.waiting = false;
                    this.exception = false;
                    this.game = {};
                })
                .listen('GameStartEvent', (e) => {

                    // alert("Второй игрок принял приглашение в игру!");
                    this.game = e.game;
                    this.message = e.message;
                    this.info = true;
                    this.play = true;
                    this.leave = true;

                    // console.log(this.game);

                    this.exception = false;
                    this.waiting = false;
                })
                .listen('FirstPlayerLeavedGameEvent', (e) => {
                    
                    // alert('Первый игрок покинул игру.');
                    this.message = e.message;
                    this.info = true;

                    this.play = false;
                    this.leave = false;
                    this.exception = false;
                    this.game = {};
                })
                .listen('SecondPlayerLeavedGameEvent', (e) => {
                    
                    // alert('Второй игрок покинул игру.');
                    this.message = e.message;
                    this.info = true;

                    this.play = false;
                    this.leave = false;
                    this.exception = false;
                    this.game = {};
                });
                
        },

}
       
</script>

<style>

    .hover-shadow:hover {
        
        box-shadow:0 .5rem 1rem rgba(0,0,0,.15);
    }

    
</style>