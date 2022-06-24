<template>

        <!-- /// Response Card /// -->
        <div  v-if="response" id="offer-block" class="col-8 text-center flex-column justify-content-center align-items-center">
            <section class="d-flex flex-column align-items-center">
                <div class="card shadow p-3 mb-5 bg-body rounded" style="width: 100%">
                    <div class="card-body bg-dark opacity-75" style="border-radius: 4px">

                        <div class="offer-card" style="width: 100%">
                            <div class="alert alert-light m-0" role="alert">
                                <h6 class="h6" > Waiting for a response from<i class="text-success"> Player two...</i></h6>
                                <div class="card-body d-flex justify-content-center align-items-center mt-3">
                                    <a href="#" class="btn btn-outline-danger btn-sm hover-shadow ml-2" style="width: 20%">Cencel</a>
                                </div>
                            </div>  
                        </div>

                    </div>
                </div>
            </section>
        </div> <!-- .col-8 -->


</template>

<script>
export default {

    props: {

        
    },

    data() {

        return {

            response: false,
        }
    },

    methods: {

        
        },

    mounted() {
        
    
    },

}


</script>

<style>

</style>