@extends('layouts.app')

@section('content')
    <div id="gebruikersIndex">
        <h1>{{ __('boodschappen.gebruikers_titel') }}</h1>
        <hr>

        <div class="row">
            <div class="input-group mb-3">
                <input type="search" class="form-control" aria-label="Search" id="zoekVeld">
                <button type="button" class="btn btn-outline-success" id="zoekKnop">
                    <i class="bi bi-search"></i>
                </button>
                <button type="button" class="btn btn-outline-success me-5" id="zoekReset">
                    <i class="bi bi-x"></i>
                </button>
                <button type="button" class="btn btn-primary" id="nieuwKnop">
                    <i class="bi bi-person-plus"></i>
                    {{ __('boodschappen.gebruikers_nieuwknop') }}
                </button>
            </div>
        </div>
        <div class="row" id="lijst"></div>
    </div>
@endsection