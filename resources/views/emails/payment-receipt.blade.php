<x-mail::message>
# {{ $forLandlord ? 'Rent received' : 'Payment receipt' }}

@if ($forLandlord)
**{{ $tenant->name }}** has paid the rent for **{{ $property->name }}**, unit **{{ $unit->unit_number }}**.
@else
Hi {{ $tenant->name }}, we have received your rent payment. Thank you!
@endif

**Property:** {{ $property->name }}, unit {{ $unit->unit_number }}

**Period:** {{ $period }}

**Amount:** ₹{{ $amount }}

**Reference:** {{ $reference }}

**Paid on:** {{ $paidAt }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
