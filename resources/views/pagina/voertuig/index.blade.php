@extends('layouts.app')

@section('content')
    <div id="voertuigIndex">
        <h1>{{ __('boodschappen.voertuig_titel') }}</h1>
        <hr>

  
        <div class="row">

            <div class="col-11">
                <div class="input-group mb-3" style="justify-content: end;">
                   {{--  <input type="search" class="form-control" aria-label="Search" id="zoekVeld">
                    <button type="button" class="btn btn-outline-success" id="zoekKnop">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success me-5" id="zoekReset">
                        <i class="bi bi-x"></i>
                    </button> --}}
                    <button type="button" class="btn btn-primary" id="nieuwKnop">
                        <i class="bi bi-car-front"></i>
                        {{ __('boodschappen.voertuigen_nieuwknop') }}
                    </button>
                </div>
            
                <div class="row" id="lijst">

                </div>
            </div>
            <div class="col-1"></div>
        </div>    
    </div>
@endsection