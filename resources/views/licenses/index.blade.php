<x-layout title="Licenses">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Licenses</h1>
        <a href="{{ route('web.licenses.create') }}" class="btn btn-primary">Create License</a>
    </div>
    <p class="text-muted">Licenses grant specific rights to a customer for a particular version and variation of a project.</p>

    <div class="card mb-4">
        <div class="card-header">Filters</div>
        <div class="card-body">
            <form action="{{ route('web.licenses.index') }}" method="GET" class="row g-3 align-items-end" id="filter-form">
                <div class="col-md-3">
                    <label for="customer_search" class="form-label">Customer</label>
                    <input type="text" class="form-control" id="customer_search" placeholder="Search Customer..." value="{{ $customers->find(request('customer_id'))?->display_name }}">
                    <input type="hidden" name="customer_id" id="customer_id" value="{{ request('customer_id') }}">
                    <div id="customer_results" class="list-group mt-1 position-absolute" style="z-index: 1000; width: 90%;"></div>
                </div>
                <div class="col-md-3">
                    <label for="project_search" class="form-label">Project</label>
                    <input type="text" class="form-control" id="project_search" placeholder="Search Project..." value="{{ $projects->find(request('project_id'))?->display_name }}">
                    <input type="hidden" name="project_id" id="project_id" value="{{ request('project_id') }}">
                    <div id="project_results" class="list-group mt-1 position-absolute" style="z-index: 1000; width: 90%;"></div>
                </div>
                <div class="col-md-2">
                    <label for="variation_id" class="form-label">Variation</label>
                    <select class="form-select" name="variation_id" id="variation_id" @if(!request('project_id')) disabled @endif>
                        <option value="">All Variations</option>
                        @foreach($variations as $variation)
                            <option value="{{ $variation->id }}" @if(request('variation_id') == $variation->id) selected @endif>{{ $variation->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="version_id" class="form-label">Version</label>
                    <select class="form-select" name="version_id" id="version_id" @if(!request('project_id')) disabled @endif>
                        <option value="">All Versions</option>
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" @if(request('version_id') == $version->id) selected @endif>{{ $version->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('web.licenses.index') }}" class="btn btn-secondary w-100 mt-2">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Display Name</th>
                <th>Customer</th>
                <th>Project</th>
                <th>Variation</th>
                <th>Version</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($licenses as $license)
                <tr>
                    <td><a href="{{ route('web.licenses.show', $license) }}">{{ $license->display_name }}</a></td>
                    <td><a href="{{ route('web.customers.show', $license->customer) }}">{{ $license->customer->display_name }}</a></td>
                    <td><a href="{{ route('web.projects.show', $license->variation->project) }}">{{ $license->variation->project->display_name }}</a></td>
                    <td><a href="{{ route('web.projects.variations.show', [$license->variation->project, $license->variation]) }}">{{ $license->variation->display_name }}</a></td>
                    <td><a href="{{ route('web.projects.versions.show', [$license->version->project, $license->version]) }}">{{ $license->version->display_name }}</a></td>
                    <td>
                        <a href="{{ route('web.licenses.edit', $license) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $licenses->links() }}

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setupAutocomplete('project', '/api/v1/projects/search', () => {
                // On project select, submit the form to reload the page with new version/variation options
                document.getElementById('filter-form').submit();
            });
            setupAutocomplete('customer', '/api/v1/customers/search');
        });

        function setupAutocomplete(type, url, onSelectCallback = null) {
            const input = document.getElementById(type + '_search');
            const hiddenInput = document.getElementById(type + '_id');
            const results = document.getElementById(type + '_results');

            input.addEventListener('keyup', async function () {
                const query = this.value;
                if (query.length < 2) {
                    results.innerHTML = '';
                    return;
                }

                try {
                    const response = await fetch(`${url}?query=${query}`);
                    const items = await response.json();
                    const data = items.data ? items.data : items;

                    results.innerHTML = data.map(item =>
                        `<a href="#" class="list-group-item list-group-item-action" data-id="${item.id}" data-name="${item.display_name}">
                            ${item.display_name}
                        </a>`
                    ).join('');
                } catch (error) {
                    console.error(`Error searching ${type}:`, error);
                }
            });

            results.addEventListener('click', function (e) {
                e.preventDefault();
                const link = e.target.closest('a');
                if (link) {
                    hiddenInput.value = link.dataset.id;
                    input.value = link.dataset.name;
                    results.innerHTML = '';
                    if (onSelectCallback) {
                        onSelectCallback();
                    }
                }
            });

            input.addEventListener('change', function() {
                if (this.value === '') {
                    hiddenInput.value = '';
                }
            });
        }
    </script>
    @endpush
</x-layout>
