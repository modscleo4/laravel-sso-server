@extends('adminlte::page')

@section('title', 'Dashboard - AdminLTE')

@if(session()->has('status'))
    <div class="alert {{ session('saved') ? 'alert-success' : 'alert-error' }} alert-dismissible"
         role="alert">
        {{ session()->get('status') }}

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
