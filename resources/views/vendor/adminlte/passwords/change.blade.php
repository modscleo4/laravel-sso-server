@extends('adminlte::master')

@section('adminlte_css')
    @yield('css')
@stop

@section('body_class', 'login-page')

@section('body')
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url(config('adminlte.dashboard_url', 'home')) }}">{!! config('adminlte.logo', '<b>Admin</b>LTE') !!}</a>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg">{{ __('adminlte.password_change_message') }}</p>
            <form action="{{ route('user.password.update') }}" method="post">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                <input type="hidden" name="return_url" value="{{ $return_url }}">

                @if(\App\Auth::user()->password_change_at != null)
                    <div class="form-group has-feedback {{ $errors->has('current_password') ? 'has-error' : '' }}">
                        <input type="password" name="current_password" class="form-control"
                               placeholder="{{ __('adminlte.current_password') }}">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        @if($errors->has('current_password'))
                            <span class="help-block">
                            <strong>{{ $errors->first('current_password') }}</strong>
                        </span>
                        @endif
                    </div>
                @endif

                <div class="form-group has-feedback {{ $errors->has('password') ? 'has-error' : '' }}">
                    <input type="password" name="password" class="form-control"
                           placeholder="{{ __('adminlte.new_password') }}">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    @if($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? 'has-error' : '' }}">
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="{{ __('adminlte.retype_password') }}">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                    @if($errors->has('password_confirmation'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                        </span>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    {{ __('adminlte.change_password') }}
                </button>

                <input type="hidden" id="inputPrevious" name="previous"
                       value="{{ $return_url ?? old('previous') ?? url()->previous() }}">
            </form>
            @if(\App\Auth::user()->password_change_at != null)
                <div class="auth-links">
                    <a href="{{ $return_url ?? old('previous') ?? url()->previous() }}"
                       class="text-center"><i class="fa fa-fw fa-arrow-left"></i>{{ trans('pagination.back') }}</a>
                </div>
            @endif
        </div>
        <!-- /.login-box-body -->
    </div><!-- /.login-box -->
@stop

@section('adminlte_js')
    @yield('js')
@stop
