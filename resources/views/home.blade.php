@extends('adminlte::page')

@section('title', 'Dashboard - AdminLTE')

@section('content')
    @if(session()->has('status'))
        <div class="alert {{ session('saved') ? 'alert-success' : 'alert-error' }} alert-dismissible"
             role="alert">
            {{ session()->get('status') }}

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        @foreach($brokers as $broker)
            <a href="{{ $broker->url }}/home">
                <div class="col-sm-4">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{ $broker->name }}</h3>

                            <p>&nbsp;</p>
                            <p>{{ $broker->url }}</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-fw fa-server"></i>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endsection
