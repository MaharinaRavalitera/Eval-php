@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head ">
                    <div class="tablet__head-toolbar">
                        <h3>@lang('All Invoices')</h3>
                    </div>
                </div>
                <div class="tablet__body">
                    <table class="table table-hover" id="invoices-table">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice Number') }}</th>
                                <th>{{ __('Client') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices ?? [] as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>
                                        @if($invoice->client)
                                            <a href="{{ route('clients.show', $invoice->client->external_id) }}">
                                                {{ $invoice->client->company_name }}
                                            </a>
                                        @else
                                            {{ __('No client') }}
                                        @endif
                                    </td>
                                    <td>{{ formatMoney($invoice->total_price) }}</td>
                                    <td>{{ $invoice->due_at ? $invoice->due_at->format(carbonDate()) : '' }}</td>
                                    <td>
                                        <span class="label label-default">{{ $invoice->status }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-link">
                                            <i class="fa fa-eye"></i> {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(empty($invoices) || count($invoices) === 0)
                        <div class="text-center">
                            <h4>{{ __('No invoices found') }}</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
