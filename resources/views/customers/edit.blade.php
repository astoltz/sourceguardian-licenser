<x-layout title="Edit Customer">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Edit Customer</h1>
        <div>
            <a href="{{ route('web.customers.show', $customer) }}" class="btn btn-info">View Customer</a>
            <a href="{{ route('web.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
        </div>
    </div>

    <form action="{{ route('web.customers.update', $customer) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" value="{{ $customer->display_name }}" required>
        </div>
        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" @if($customer->enabled) checked @endif>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <hr>

        <x-key-value-manager label="Customer Constants" name="customer_constants" :items="$customer->customerConstants" />
        <x-list-manager label="Customer Header Texts" name="customer_header_texts" :items="$customer->customerHeaderTexts" />

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</x-layout>
