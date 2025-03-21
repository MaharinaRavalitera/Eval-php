@extends('layouts.master')
@section('heading')
    {{ __('Gestion de la base de données') }}
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="sidebarheader">
                <p>{{ __('Réinitialisation de la base de données') }}</p>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="alert alert-danger">
                <strong>{{ __('Attention!') }}</strong>
                {{ __('Cette action va vider toutes les tables de la base de données. Cette opération est irréversible.') }}
            </div>
        </div>

        <div class="col-lg-6">
            <div class="tablet">
                <div class="tablet__head">
                    <div class="tablet__head-label">
                        <h3 class="tablet__head-title">{{ __('Options de réinitialisation') }}</h3>
                    </div>
                </div>
                <div class="tablet__body">
                    <div class="tablet__items">
                        <form action="{{ route('settings.database.truncate') }}" method="POST" id="truncate-form">
                            {{ csrf_field() }}
                            
                            <div class="form-group">
                                <label class="control-label">{{ __('Actions à effectuer:') }}</label>
                                <ul class="list-unstyled">
                                    <li>✓ {{ __('Désactiver temporairement les contraintes') }}</li>
                                    <li>✓ {{ __('Vider toutes les tables') }}</li>
                                    <li>✓ {{ __('Réinitialiser les auto-incréments') }}</li>
                                    <li>✓ {{ __('Réactiver les contraintes') }}</li>
                                    <li>✓ {{ __('Préserver l\'utilisateur administrateur') }}</li>
                                    <li>✓ {{ __('Réinsérer les données initiales') }}</li>
                                </ul>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-brand" onclick="return confirm('{{ __('Êtes-vous sûr de vouloir réinitialiser la base de données ?') }}')">
                                    {{ __('Réinitialiser la base de données') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
    $(document).ready(function() {
        // Removed the submit event handler
    });
</script>
@endpush
