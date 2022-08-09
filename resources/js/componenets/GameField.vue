
<template>
    
    <!-- /// Side Bar /// --> 
    <div id="side-bar" class="col-4 position-relative">

        <!-- alert error -->
        <section v-if="errored_users" class="d-flex flex-column align-items-center">
            <div class="card-info" style="width: 80%">
                <div class="alert  text-center" role="alert">
                    <strong class="text-secondary">Error!</strong><div class="text-secondary">Users data could not be retrieved. Try again later...</div>
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
        <section v-else>
            <div class="cards d-flex flex-column justify-content-center align-items-center shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                
                <!-- cards -->
                <div  class="card-body bg-dark opacity-75" style="width: 100%; border-radius: 4px">
                    <div v-for="user of users" :key="user.id" class="card text-center mt-1">
                        <div class="card-body d-flex flex-column  align-items-center">
                        <h6 class="h6 card-title text-secondary">
                             {{ (user.name)? user.name : '' }} <small v-show="user.can_play" class="text-success">free</small> <small v-show="!user.can_play" class="text-danger">is playing</small>  </h6>
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
                            <h6 class="h6" ><i class="text-success"> {{  (game.player_1.name)? game.player_1.name : ''  }} </i> offers to play the game!</h6>
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
                            <h6 class="h6" > Waiting for a response from <i class="text-success"> {{  (game.player_2.name)? game.player_2.name : '' }} </i> ...</h6>
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
                    <div class="text-secondary"> {{ (message)? message : 'The message was not received.' }} </div>
                </div>  
            </div>
        </section>

         <!-- alert info -->
        <section v-if="info" class="d-flex flex-column align-items-center">
            <div class="card-info shadow" style="width: 100%">
                <div class="alert alert-success mb-0 p-y-1" role="alert">
                    <div class="text-secondary"> {{ (message)? message : 'The message was not received.' }} </div>
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
                            <h6 class="h6 card-title text-secondary"><i class="text-success"> Round Time:</i> {{ (timer.display)? timer.display : '00:00' }} sec. </h6>
                            </div>
                        </div>

                        <!-- figures -->
                        <div class="card text-center mt-1">
                            <div class="btn-group p-3" role="group" aria-label="Figures" style="width: 100%">
                                <button :disabled="finished" v-on:click="makeMove(ROCK)" class="btn btn-outline-success hover-shadow" style="width: 50%">Rock</button>
                                <button :disabled="finished" v-on:click="makeMove(SCISSORS)" class="btn btn-outline-success hover-shadow" style="width: 50%">Scissors</button>
                                <button :disabled="finished" v-on:click="makeMove(PAPER)" class="btn btn-outline-success hover-shadow" style="width: 50%">Paper</button>
                                <button :disabled="finished" v-on:click="makeMove(LIZARD)" class="btn btn-outline-success hover-shadow" style="width: 50%">Lizard</button>
                                <button :disabled="finished" v-on:click="makeMove(SPOCK)" class="btn btn-outline-success hover-shadow" style="width: 50%">Spoke</button>
                            </div>
                        </div>

                        <!-- round results -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start" style="height: 11.5rem">
                                <h6 class="h6 card-title text-success py-3"><i>Round Results</i></h6>
                                <h6 v-show="visible" class="h6 card-title text-secondary">Player One : <span class="text-success"> {{ this.getFigureName( (historyLastRound)? historyLastRound.move_player_1 : null ) }} </span></h6>
                                <h6 v-show="visible" class="h6 card-title text-secondary">Player Two: <span class="text-success"> {{ this.getFigureName( (historyLastRound)? historyLastRound.move_player_2 : null ) }} </span></h6>
                                <h6 v-show="visible" class="h6 card-title text-secondary">Winner: 
                                    <span class="text-success"> {{  winnedPlayerNameOrDraw  }} </span>
                                </h6>                                                                                                                          <!--(historyLastRound)? historyLastRound.draw : null -->
                            </div>
                        </div>
                        
                        <!-- score users -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start" style="height: 10rem">
                                <h6 class="h6 card-title text-success py-3"><i>Score</i></h6>
                                <h6 v-for="player of playersVictories" :key="player.id" class="h6 card-title text-secondary"> {{ (game.player_1.id == player.winned_player)? `${game.player_1.name} - Player One: ` : `${game.player_2.name} - Player Two: ` }} <span class="text-success"> {{ (player.victory_count)?  player.victory_count : '' }} </span></h6>
                            </div>
                        </div>

                        <!-- game history -->
                        <div class="card text-center mt-1">
                            <div class="card-body d-flex flex-column justify-content-start align-items-start">
                                <h6 class="h6 card-title text-success pt-3 pb-1"><i>Game History </i></h6>

                                <div v-for="round of history" :key="round.number" class="my-0 py-3 d-flex flex-column justify-content-start align-items-start  border-bottom">
                                    <h6 class="h6 card-title text-success">Round : <span class="text-secondary"> {{ (round.number)? round.number : '' }} </span></h6>
                                    <h6 class="h6 card-title text-secondary">Player One : <span class="text-success"> {{ this.getFigureName(round.move_player_1) }} </span></h6>
                                    <h6 class="h6 card-title text-secondary">Player Two: <span class="text-success"> {{ this.getFigureName(round.move_player_2) }} </span></h6>
                                    <h6 class="h6 card-title text-secondary">Winner: 
                                        <span class="text-success"> {{ (round.winned_player)? getWinnerName(round.winned_player) : this.getDrawDescription(round.draw) }} </span>
                                    </h6>
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

                history: [],

                historyLastRound: {},

                playersVictories: [],

                timer: {

                    display: '00:00',
                    totalSeconds: 0,
                    intervalId: 0,
                    minutes: 0,
                    seconds: 0,
                },

                pagination: {},
                
                loading: false,
                errored: false,

                loading_users: false,
                errored_users: false,

                offer:    false,
                waiting:  false,
                play:     false,
                leave:    false,
                visible:  false,

                message: '',
                exception: false,
                info: false,

                finished: false,

                NONE: 0,
                ROCK: 1,
                SCISSORS: 2,
                PAPER: 3,
                LIZARD: 4,
                SPOCK: 5,

                round: 1,
            }
        },

        computed: {

            winnedPlayerNameOrDraw() {

                if (this.historyLastRound == null) {

                    return ' None Data ';
                }

                if (this.historyLastRound.winned_player == null) {

                    return ' Draw ';
                }
                else {

                    return this.getWinnerName(this.historyLastRound.winned_player);
                }

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
                        
                            // this.message = response.data.data.message;
                            // this.exception = true;
                        }
                        else {

                            this.game = response.data.data.game;
                            this.round = response.data.data.game.lastFinishedRound.number + 1;
                            this.history = response.data.data.game.history;
                            this.historyLastRound = response.data.data.game.historyLastRound;
                            this.playersVictories = response.data.data.game.playersVictories;
                            this.finished = response.data.data.game.finished;
                            

                            this.waiting = response.data.data.waiting;
                            this.offer = response.data.data.offer;
                            this.play = response.data.data.play;
                            this.leave = response.data.data.leave;

                            
                            this.timer.totalSeconds = response.data.data.game.remainingTimeOfRound;
                            this.startTimer();
                            
                            // console.log(this.timer);
                            console.log(`Раунд после перезагрузки страницы: ${this.round}`);
                            console.log(`История последнего раунда: `);
                            console.log(response.data.data.game.historyLastRound);
                            console.log(`Ничья последнего раунда: ${response.data.data.game.historyLastRound.draw}`);
                            console.log(`Время после перезагрузки страницы: ${response.data.data.game.remainingTimeOfRound}`);
                            console.log(response.data.data.game.history);
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
                        this.play = false;
                        this.history = [];
                        // console.log(this.game);
                    }  
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


            acceptInvite(game) {

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }
               
                axios.post(`api/v1/accept-invite/${game.id}`, {

                    _method: 'PUT',
                   
                }, config)
                .then( response => {

                    this.game = response.data.data;
                    this.timer.totalSeconds = response.data.data.remainingTimeOfRound;
                    this.history = [];
                    this.playersVictories = [];

                    this.exception = false;
                    this.offer = false;
                    this.finished = false;
                    this.play = true;
                    this.leave = true;

                    this.startTimer();
                    console.log(this.game);
                })
                .catch( error => {

                    console.log(error);
                });
            },


            rejectInvite(game) {

                if (!confirm('Are you sure you want to reject the game?')) {

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

                if (!confirm('Are you sure you want to leave the game?')) {

                    return false;
                }

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post(`api/v1/leave-game/${game.id}`, {

                  _method: 'PUT'
                    
                }, config)
                .then( response => {

                    this.message = 'You leaved the game!';
                    this.info = true;

                    this.play = false;
                    this.leave = false;
                    this.exception = false;
                    
                    this.game = {}
                    this.playersVictories = [];

                    this.stopTimer();
                })
                .catch( error => {

                    console.log(error);
                });
            },


            makeMove(figure) {

                console.log(`Сделан ход в раунде ${this.round}`);

                let config = {

                    headers: {
                        Authorization: "Bearer " + this.token,
                    }
                }

                axios.post('api/v1/make-move', {

                    game_id: this.game.id,
                    round_number: this.round,
                    figure: figure,
                    
                }, config)
                .then( response => {

                    if (response.data.data.exception) {

                        this.message = response.data.data.message;
                        this.exception = true;
                        this.info = false;
                    }
                    else {

                        this.message = 'You made a move.';
                        this.exception = false;
                        this.info = true;
                    }
                })
                .catch( error => {

                    console.log(error);
                });
            },


            getFigureName(figure) {

                if (figure == null) {

                    return  ' None Data ';
                }

                switch(figure) {

                    case this.NONE:  return 'None';
                    case this.ROCK:  return ' Rock';
                    case this.SCISSORS:  return ' Scissors';
                    case this.PAPER:  return ' Papper';
                    case this.LIZARD:  return ' Lizzard';
                    case this.SPOCK:  return ' Spoke';
                }
            },


            getWinnerName(winned_player) {
                
                if (winned_player == null) {

                    return ' None Data ';
                }
                
                if (winned_player == this.game.player_1.id) {

                    return this.game.player_1.name;
                }

                if (winned_player == this.game.player_2.id) {

                    return this.game.player_2.name;
                }
            },


            getDrawDescription(draw) {
                
                if (draw == null) {

                    return ' None Data ';
                }

                if (draw == 0) {

                    return ' None';
                }

                return ' Draw ';
            },


            hideHistoryLastRound() {

                setTimeout(() => this.visible = false, 5000);
            },




            startTimer() {

                let self = this;

                self.timer.intervalId = setInterval(() => {
                
                    self.timer.minutes = parseInt(self.timer.totalSeconds / 60, 10);
                    self.timer.seconds = parseInt(self.timer.totalSeconds % 60, 10);

                    self.timer.minutes = self.timer.minutes < 10 ? '0' + self.timer.minutes : self.timer.minutes;
                    self.timer.seconds = self.timer.seconds < 10 ? '0' + self.timer.seconds : self.timer.seconds;
                    self.timer.display = self.timer.minutes + ":" + self.timer.seconds;
                    --self.timer.totalSeconds;

                    if ( self.timer.totalSeconds <= 0) {

                        self.stopTimer();
                    }
                    
                }, 1000);
            },


            clearTimer() {

                this.timer.display = '00:00';
                this.timer.totalSeconds = 0;
                this.timer.intervalId = 0;
            },


            stopTimer() {

                clearInterval(this.timer.intervalId);
                this.clearTimer();
            },

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
                    this.play = false;
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
                    this.timer.totalSeconds = e.game.remainingTimeOfRound;

                    this.message = e.message;
                    this.info = true;
                    this.play = true;
                    this.leave = true;
                    this.startTimer();

                    this.playersVictories = [];

                    this.exception = false;
                    this.waiting = false;
                    this.finished = false;
                    // console.log(e.game);
                })
                .listen('FirstPlayerLeavedGameEvent', (e) => {
                    
                    // alert('Первый игрок покинул игру.');
                    this.message = e.message;
                    this.info = true;

                    this.play = false;
                    this.leave = false;
                    this.exception = false;

                    this.game = {};
                    this.playersVictories = [];
                    this.stopTimer();
                })
                .listen('SecondPlayerLeavedGameEvent', (e) => {
                    
                    // alert('Второй игрок покинул игру.');
                    this.message = e.message;
                    this.info = true;

                    this.play = false;
                    this.leave = false;
                    this.exception = false;

                    this.game = {};
                    this.playersVictories = [];
                    this.stopTimer();
                })
                .listen('FirstPlayerMadeMoveEvent', (e) => {
                    
                    // alert('Первый игрок сделал ход.');
                    this.message = e.message;
                    this.info = true;
                    this.exception = false;

                    this.game = e.game;
                    // console.log(e.game);
                })
                .listen('SecondPlayerMadeMoveEvent', (e) => {
                    
                    // alert('Второй игрок сделал ход.');
                    this.message = e.message;
                    this.info = true;
                    this.exception = false;

                    this.game = e.game;
                    // console.log(e.game);
                })
                .listen('RoundTimerRestartEvent', (e) => {
                    
                    // alert('Игроки не сделали ходов.');
                    this.stopTimer();

                    this.message = e.message;
                    this.info = true;
                    this.exception = false;

                    this.timer.totalSeconds = e.game.remainingTimeOfRound;
                    this.startTimer();

                    this.game = e.game;
                    // console.log(e.game);
                    console.log(e.game.remainingTimeOfRound);
                }) 
                .listen('GameRoundFinishedEvent', (e) => {

                    // alert('Раунд завершён!')
                    this.stopTimer();

                    this.message = e.message;
                    this.info = true;
                    this.exception = false;

                    this.game = e.game;
                    this.round = e.game.lastFinishedRound.number + 1;
                    this.history = e.game.history;

                    this.historyLastRound = e.game.historyLastRound;
                    this.visible = true;
                    this.hideHistoryLastRound();

                    this.playersVictories = e.game.playersVictories;

                    // console.log(e.game);
                    console.log(`Раунд ${e.game.lastFinishedRound.number} завершён`);
                    console.log( this.historyLastRound);
                })
                .listen('GameNewRoundStartEvent', (e) => {

                    // alert('Новый Раунд!')
                    this.timer.totalSeconds = e.game.remainingTimeOfRound;
                    this.startTimer();

                    this.message = e.message;
                    this.info = true;
                    this.exception = false;

                    this.game = e.game;
                    // console.log(e.game); 
                    console.log(`Раунд ${this.round} начат.`);
                })
                .listen('GameFinishEvent', (e) => {

                    console.log('Игра окончена!');
                    
                    this.message = e.message;
                    this.info = true;
                    this.exception = false;
                    this.leave = false;
                    this.finished = true;
                    
                    this.game = e.game;
                    this.round = 1;

                    this.stopTimer();

                    console.log(this.game);
                });
                
        },

}
       
</script>

<style>

    .hover-shadow:hover {
        
        box-shadow:0 .5rem 1rem rgba(0,0,0,.15);
    }

    
</style>