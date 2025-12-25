<x-layout :title="$license->display_name">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>{{ $license->display_name }}</h1>
        <div>
            <a href="{{ route('web.licenses.edit', $license) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('web.licenses.destroy', $license) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        </div>
    </div>
    <p>Customer: <a href="{{ route('web.customers.show', $license->customer) }}">{{ $license->customer->display_name }}</a></p>
    <p>Project: <a href="{{ route('web.projects.show', $license->variation->project) }}">{{ $license->variation->project->display_name }}</a></p>
    <p>Variation: <a href="{{ route('web.projects.variations.show', [$license->variation->project, $license->variation]) }}">{{ $license->variation->display_name }}</a></p>
    <p>Version: <a href="{{ route('web.projects.versions.show', [$license->version->project, $license->version]) }}">{{ $license->version->display_name }}</a></p>

    @if ($license->expiration_date || $license->bind_machine_id || $license->licenseDomains->isNotEmpty() || $license->licenseIps->isNotEmpty() || $license->licenseMacs->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Restrictions</div>
            <div class="card-body">
                @if($license->expiration_date)
                    <p><strong>Expiration Date:</strong> {{ $license->expiration_date->format('Y-m-d') }}</p>
                @endif
                @if($license->bind_machine_id)
                    <p><strong>Machine ID:</strong> <code>{{ $license->bind_machine_id }}</code></p>
                @endif
                @if($license->licenseDomains->isNotEmpty())
                    <p><strong>Domains:</strong> {{ $license->licenseDomains->pluck('domain')->join(', ') }}</p>
                @endif
                @if($license->licenseIps->isNotEmpty())
                    <p><strong>IPs:</strong> {{ $license->licenseIps->pluck('ip')->join(', ') }}</p>
                @endif
                @if($license->licenseMacs->isNotEmpty())
                    <p><strong>MACs:</strong> {{ $license->licenseMacs->pluck('mac')->join(', ') }}</p>
                @endif
            </div>
        </div>
    @endif

    @if($effectiveConstants->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Effective Constants</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($effectiveConstants as $constant)
                        <tr>
                            <td><code>{{ $constant['key'] }}</code></td>
                            <td><code>{{ $constant['value'] }}</code></td>
                            <td><x-source-badge :source="$constant['source']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($effectiveHeaderTexts->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Effective Header Texts</div>
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Text</th>
                        <th>Order</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($effectiveHeaderTexts as $text)
                        <tr>
                            <td>{{ $text['value'] }}</td>
                            <td>{{ $text['order'] }}</td>
                            <td><x-source-badge :source="$text['source']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Generated License File</span>
            <a href="{{ route('web.licenses.download', $license) }}" class="btn btn-sm btn-success">
                {{ $license->generatedLicenses->first() ? 'Download' : 'Generate' }}
            </a>
        </div>
        @if ($generated = $license->generatedLicenses->first())
            <div class="card-body">
                <p><strong>Generated on:</strong> {{ $generated->created_at }}</p>
                @if($generated->downloaded_at)
                    <p><strong>Last Downloaded:</strong> {{ $generated->downloaded_at }} by {{ $generated->downloaded_ip }}</p>
                @else
                    <p><em>Not yet downloaded.</em></p>
                @endif
                <form action="{{ route('web.licenses.reset', $license) }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Are you sure you want to clear the cache?')">Reset Cache</button>
                </form>
            </div>
        @else
            <div class="card-body">
                <p>No license file has been generated for this configuration yet.</p>
            </div>
        @endif
    </div>

    <a href="{{ route('web.licenses.index') }}" class="btn btn-secondary">Back to Licenses</a>
</x-layout>
