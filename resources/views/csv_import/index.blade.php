@extends('layouts.master')
@section('heading')
    {{ __('Import CSV Data') }}
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ __('Import CSV Data') }}</h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <p><strong>{{ __('Information') }}:</strong> {{ __('This feature allows you to import data from CSV files into your CRM.') }}</p>
                        <p>{{ __('Select a table, upload your CSV file, and map the columns to import your data.') }}</p>
                    </div>
                    
                    <form action="{{ route('csv_import.upload') }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        
                        <div class="form-group">
                            <label for="table">{{ __('Select Target Table') }}</label>
                            <select name="table" id="table" class="form-control" required>
                                <option value="">{{ __('-- Select a table --') }}</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table }}">{{ $table }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="csv_file">{{ __('CSV File') }}</label>
                            <input type="file" name="csv_file" id="csv_file" class="form-control" required accept=".csv,.txt">
                            <p class="help-block">{{ __('Maximum file size: 10MB') }}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="delimiter">{{ __('CSV Delimiter') }}</label>
                            <select name="delimiter" id="delimiter" class="form-control" required>
                                <option value=",">{{ __('Comma (,)') }}</option>
                                <option value=";">{{ __('Semicolon (;)') }}</option>
                                <option value="\t">{{ __('Tab') }}</option>
                                <option value="|">{{ __('Pipe (|)') }}</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="has_header" value="1" checked> 
                                    {{ __('File has header row') }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ __('Upload & Continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
