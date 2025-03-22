@extends('layouts.master')
@section('heading')
    {{ __('Map CSV Columns') }}
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{{ __('Map CSV Columns to Table Fields') }}</h3>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">
                        <p><strong>{{ __('Information') }}:</strong> {{ __('Map each column from your CSV file to the corresponding field in the database table.') }}</p>
                        <p>{{ __('Select "Do not import" for columns you want to skip.') }}</p>
                    </div>
                    
                    <h4>{{ __('Preview of CSV Data') }}</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    @foreach($preview[0] as $index => $header)
                                        <th>
                                            {{ $has_header ? $header : __('Column') . ' ' . ($index + 1) }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $rowIndex => $row)
                                    @if(!$has_header || $rowIndex > 0)
                                        <tr>
                                            @foreach($row as $cell)
                                                <td>{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <form action="{{ route('csv_import.process') }}" method="POST">
                        {{ csrf_field() }}
                        
                        <h4>{{ __('Column Mapping') }}</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('CSV Column') }}</th>
                                        <th>{{ __('Database Field') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($preview[0] as $index => $header)
                                        <tr>
                                            <td>
                                                <strong>{{ $has_header ? $header : __('Column') . ' ' . ($index + 1) }}</strong>
                                            </td>
                                            <td>
                                                <select name="column_mapping[{{ $index }}]" class="form-control">
                                                    <option value="">{{ __('-- Do not import --') }}</option>
                                                    @foreach($table_columns as $column)
                                                        <option value="{{ $column }}" 
                                                            {{ $has_header && strtolower($header) === strtolower($column) ? 'selected' : '' }}>
                                                            {{ $column }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">{{ __('Import Data') }}</button>
                            <a href="{{ route('csv_import.index') }}" class="btn btn-default">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
