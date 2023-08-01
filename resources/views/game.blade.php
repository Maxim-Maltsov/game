@extends('layouts.my-app')

@section('content')
    <div class="row mt-3 justify-content-between align-items-start gx-5">
        <game-field  token="{{ $token }}" auth_id="{{ $auth_id }}" ></game-field> <!-- GameField Vue component. -->
    </div>
@endsection
