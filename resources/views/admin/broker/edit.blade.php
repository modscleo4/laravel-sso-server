@extends('adminlte::page')

@section('title', 'Editar Broker')

@section('content_header')
    <h1>Editar Broker</h1>
@stop

@section('content')
    <form class="form-horizontal" action="{{ route('admin.broker.update', $broker->id) }}" method="post">
        @method('PUT')
        @csrf

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Dados do Broker</h3>
            </div>

            <div class="box-body">
                <div class="form-group @if($errors->has('name')) has-error @endif">
                    <label for="inputName" class="col-sm-2 control-label">Nome*</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputName" name="name"
                               placeholder="sge"
                               value="{{ old('name') ?? $broker->name }}"/>

                        <span class="help-block">{{ $errors->first('name') }}</span>
                    </div>
                </div>

                <div class="form-group @if($errors->has('url')) has-error @endif">
                    <label for="inputUrl" class="col-sm-2 control-label">URL*</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="inputUrl" name="url"
                               placeholder="http://200.145.153.1/sge"
                               value="{{ old('url') ?? $broker->url }}"/>

                        <span class="help-block">{{ $errors->first('url') }}</span>
                    </div>
                </div>

                <div class="form-group @if($errors->has('roles')) has-error @endif">
                    <label for="inputRoles" class="col-sm-2 control-label">Grupos*</label>

                    <div class="col-sm-10">
                        <select class="form-control selection" id="inputRoles" name="roles[]" multiple>

                            @foreach($roles as $role)

                                <option value="{{ $role->id }}"
                                    {{ (old('roles') ? in_array($role->id, old('roles')) : $role->hasPermissionTo($broker->name)) ? 'selected' : '' }}>
                                    {{ $role->friendly_name }}
                                </option>

                            @endforeach

                        </select>

                        <span class="help-block">{{ $errors->first('roles') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary pull-right">Salvar</button>

                <input type="hidden" id="inputPrevious" name="previous"
                       value="{{ old('previous') ?? url()->previous() }}">
                <a href="{{ old('previous') ?? url()->previous() }}" class="btn btn-default">Cancelar</a>
            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('.selection').select2({
                language: "pt-BR"
            });

            jQuery(':input').inputmask({removeMaskOnSubmit: true});
        });
    </script>
@endsection
