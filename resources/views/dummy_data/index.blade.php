@extends('layouts.master')
@section('heading')
    {{ __('Generate Test Data') }}
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ __('Generate Test Data') }}</h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <p><strong>{{ __('Information') }}:</strong> {{ __('This feature allows you to generate test data for your CRM.') }}</p>
                        <p>{{ __('The existing data in your database will be preserved.') }}</p>
                    </div>
                    
                    <form action="{{ route('dummy_data.generate') }}" method="POST">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <p>{{ __('Click the button below to generate test data:') }}</p>
                            <button type="submit" class="btn btn-primary">{{ __('Generate Test Data') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
