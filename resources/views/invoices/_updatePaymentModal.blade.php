<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="myModalLabel">
        {{ __('Add Payment') }}</h4>

</div>
<form action="{{route('payment.add', [$invoice->external_id])}}" method="POST" id="paymentForm">
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <p class="text-align-center" style="font-size:1.4em; font-weight: 300;">@lang('Amount due')</p>
            <p class="text-align-center lead" style="font-size:2.4em; line-height: 1; font-weight: 300;">{{$amountDueFormatted}}</p>
            <hr>
        </div>
        <div class="col-lg-6">
            <div class="form-group col-lg-6 removeleft">
                <label for="amount" class="thin-weight">@lang('Amount in') {{$amountDue->getCurrency()->getCode()}}</label>
                <input type="number" step=".01" name="amount" id="amount" value="{{$amountDue->getBigDecimalAmount()}}" max="{{$amountDue->getBigDecimalAmount()}}" class="form-control input-sm">
                <div class="text-danger" id="amount-error" style="display: none;">
                    {{ __('Le montant saisi dépasse le montant dû. Veuillez entrer un montant valide.') }}
                </div>
            </div>
            <div class="form-group col-lg-12 removeleft" >
                <label for="payment_date" class="thin-weight">@lang('Payment date')</label>
                <input type="date" name="payment_date" id="payment_date" data-value="{{today()->format(carbonDate())}}" class="form-control input-sm">
            </div>
            <div class="form-group col-lg-12 removeleft" >
                <label for="source" class="thin-weight">@lang('Source')</label>
                <select name="source" id="source" class="form-control">
                    @foreach($paymentSources as $paymentSource)
                        <option value="{{$paymentSource->getSource()}}">{{$paymentSource->getDisplayValue()}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{csrf_field()}}
        <div class="form-group col-lg-12" >
            <label for="source" class="thin-weight">@lang('Description')</label>
            <textarea name="description" id="description" class="form-control"></textarea>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default col-lg-6"
            data-dismiss="modal">{{ __('Close') }}</button>
    <div class="col-lg-6">
        <input type="submit" value="{{__('Register payment')}}" class="btn btn-brand form-control closebtn" id="submitPayment">
    </div>
</div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        const amountError = document.getElementById('amount-error');
        const submitButton = document.getElementById('submitPayment');
        const maxAmount = {{$amountDue->getBigDecimalAmount()}};
        
        // Fonction de validation du montant
        function validateAmount() {
            const amount = parseFloat(amountInput.value);
            if (amount > maxAmount) {
                amountError.style.display = 'block';
                submitButton.disabled = true;
                return false;
            } else {
                amountError.style.display = 'none';
                submitButton.disabled = false;
                return true;
            }
        }
        
        // Écouter les changements sur le champ de montant
        amountInput.addEventListener('input', validateAmount);
        
        // Validation au moment de la soumission du formulaire
        document.getElementById('paymentForm').addEventListener('submit', function(event) {
            if (!validateAmount()) {
                event.preventDefault();
            }
        });
        
        // Validation initiale
        validateAmount();
    });
</script>

@push('scripts')
    <script>

        $('#payment_date').pickadate({
            hiddenName:true,
            format: "{{frontendDate()}}",
            formatSubmit: 'yyyy/mm/dd',
            closeOnClear: false,
        });
    </script>
@endpush
