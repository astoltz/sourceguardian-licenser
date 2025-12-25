<x-layout title="Customers">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Customers</h1>
        <a href="{{ route('web.customers.create') }}" class="btn btn-primary">Create Customer</a>
    </div>
    <p class="text-muted">Customers represent the end-users or organizations that will be assigned licenses.</p>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Enabled</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td><a href="{{ route('web.customers.show', $customer) }}">{{ $customer->display_name }}</a></td>
                    <td>{{ $customer->enabled ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('web.customers.edit', $customer) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('web.customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $customers->links() }}
</x-layout>
