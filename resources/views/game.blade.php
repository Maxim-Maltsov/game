@extends('layouts.my-app')


@section('content')

    <div class="row mt-3 justify-content-between align-items-start gx-5">
        
        {{-- <side-bar token="{{ $token }}" auth_id="{{ $auth_id }}"></side-bar> <!-- SideBar Vue component. -->
        <offer-card></offer-card> <!-- OfferCard Vue component. -->
        <response-card></response-card> <!-- ResponseCard Vue component. -->
        <playing-field></playing-field> <!-- PlayingField Vue component. --> --}}

        <game-field  token="{{ $token }}" auth_id="{{ $auth_id }}" ></game-field> <!-- SideBar Vue component. -->

       
    </div> <!-- .row -->
    
@endsection

{{-- <script> var token = "{{ $token }}"; </script> --}}