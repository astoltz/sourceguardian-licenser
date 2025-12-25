<x-layout :title="$customer->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $customer->display_name }}</h1>
        <div>
            <a href="{{ route('web.licenses.index', ['customer_id' => $customer->id]) }}" class="btn btn-info">View Licenses</a>
            <a href="{{ route('web.customers.edit', $customer) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('web.customers.destroy', $customer) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>

    @if($customer->customerConstants->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Constants</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->customerConstants as $constant)
                        <tr>
                            <td><code>{{ $constant->key }}</code></td>
                            <td><code>{{ $constant->data }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($customer->customerHeaderTexts->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Header Texts</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Text</th>
                        <th>Order</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->customerHeaderTexts as $text)
                        <tr>
                            <td>{{ $text->data }}</td>
                            <td>{{ $text->order }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('web.customers.index') }}" class="btn btn-secondary">Back to Customers</a>
</x-layout>
