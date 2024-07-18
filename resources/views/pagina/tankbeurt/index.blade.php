@extends('layouts.app')

@section('content')
    <div id="tankbeurtenIndex">
        <h1>{{ __('boodschappen.tankbeurt_titel') }} {{  $description }}</h1>
        


        <div class="row">
            <input type="hidden" id="voertuigBewerkID" value="{{  $voertuigID }}">
           <!-- <div class="col-12"> -->
                <div class="input-group mb-3" style="justify-content: end;">
                    {{-- <input type="search" class="form-control" aria-label="Search" id="zoekVeld">
                    <button type="button" class="btn btn-outline-success" id="zoekKnop">
                        <i class="bi bi-search"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success me-5" id="zoekReset">
                        <i class="bi bi-x"></i>
                    </button> --}}
                    <button type="button" class="btn btn-primary" id="nieuwKnop">
                        <i class="bi bi-fuel-pump"></i>
                        {{ __('boodschappen.tankbeurt_nieuwknop') }}
                    </button>
                </div>
            
                <div class="row" id="lijst">

                </div>
            <!-- </div> -->
            <div class="col-0"></div>
        </div>    
    </div>
@endsection