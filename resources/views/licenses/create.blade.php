<x-layout title="Create License">
    <h1>Create License</h1>
    <p class="text-muted">Create a new license configuration. This defines the rules and restrictions for a license that can be generated and downloaded.</p>

    <form action="{{ route('web.licenses.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="display_name" class="form-label">Display Name</label>
            <input type="text" class="form-control" id="display_name" name="display_name" placeholder="Leave blank to auto-generate">
            <div class="form-text">A descriptive name for this license configuration (e.g., "Blue Jay - Pro License").</div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="project_id" class="form-label">Project</label>
                    <select class="form-select" id="project_id" name="project_id" required>
                        <option value="">Select a Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->display_name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">The project this license belongs to.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="version_id" class="form-label">Version</label>
                    <select class="form-select" id="version_id" name="version_id" required disabled>
                        <option value="">Select a project first</option>
                    </select>
                    <div class="form-text">The specific software version this license is for.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="variation_id" class="form-label">Variation</label>
                    <select class="form-select" id="variation_id" name="variation_id" required disabled>
                        <option value="">Select a project first</option>
                    </select>
                    <div class="form-text">The product variation (e.g., Standard, Pro) this license is for.</div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="customer_search" class="form-label">Customer</label>
            <input type="text" class="form-control" id="customer_search" placeholder="Search for a customer...">
            <input type="hidden" id="customer_id" name="customer_id">
            <div id="customer_search_results" class="list-group mt-1"></div>
            <div id="selected_customer" class="mt-2"></div>
            <div class="form-text">The customer who owns this license.</div>
        </div>

        <div class="mb-3">
            <label for="expiration_date" class="form-label">Expiration Date</label>
            <div class="input-group">
                <input type="date" class="form-control" id="expiration_date" name="expiration_date">
                <button type="button" class="btn btn-outline-secondary" onclick="setDate(7)">+7d</button>
                <button type="button" class="btn btn-outline-secondary" onclick="setDate(30)">+1m</button>
                <button type="button" class="btn btn-outline-secondary" onclick="setDate(90)">+3m</button>
                <button type="button" class="btn btn-outline-secondary" onclick="setDate(365)">+1y</button>
            </div>
            <div class="form-text">The date the generated license file will expire.</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="bind_domain_ignore_cli" name="bind_domain_ignore_cli" value="1">
                    <label class="form-check-label" for="bind_domain_ignore_cli">Ignore Domain Check for CLI</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="bind_ip_ignore_cli" name="bind_ip_ignore_cli" value="1">
                    <label class="form-check-label" for="bind_ip_ignore_cli">Ignore IP Check for CLI</label>
                </div>
            </div>
        </div>

        <hr>

        <x-list-manager label="Bind Domains" name="license_domains" />
        <x-list-manager label="Bind IPs" name="license_ips" />
        <x-list-manager label="Bind MACs" name="license_macs" />
        <x-simple-list-manager label="Bind Machine IDs" name="license_machine_ids" />

        <hr>

        <x-key-value-manager label="License Constants" name="license_constants" />
        <x-list-manager label="License Header Texts" name="license_header_texts" />

        <div class="mb-3 form-check">
            <input type="hidden" name="enabled" value="0">
            <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" checked>
            <label class="form-check-label" for="enabled">Enabled</label>
        </div>

        <button type="submit" class="btn btn-primary">Create</button>
    </form>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projectId = document.getElementById('project_id');
            const versionId = document.getElementById('version_id');
            const variationId = document.getElementById('variation_id');
            const customerSearch = document.getElementById('customer_search');
            const customerId = document.getElementById('customer_id');
            const customerResults = document.getElementById('customer_search_results');
            const selectedCustomer = document.getElementById('selected_customer');

            // Dependent Dropdowns
            projectId.addEventListener('change', async function () {
                const selectedProjectId = this.value;
                versionId.disabled = true;
                variationId.disabled = true;
                versionId.innerHTML = '<option value="">Loading...</option>';
                variationId.innerHTML = '<option value="">Loading...</option>';

                if (!selectedProjectId) {
                    versionId.innerHTML = '<option value="">Select a project first</option>';
                    variationId.innerHTML = '<option value="">Select a project first</option>';
                    return;
                }

                try {
                    const response = await fetch(`/api/v1/projects/${selectedProjectId}`);
                    const project = await response.json();

                    versionId.innerHTML = project.versions.map(v => `<option value="${v.id}">${v.display_name}</option>`).join('');
                    variationId.innerHTML = project.variations.map(v => `<option value="${v.id}">${v.display_name}</option>`).join('');

                    versionId.disabled = false;
                    variationId.disabled = false;
                } catch (error) {
                    console.error('Error fetching project details:', error);
                    versionId.innerHTML = '<option value="">Error loading</option>';
                    variationId.innerHTML = '<option value="">Error loading</option>';
                }
            });

            // Customer Autocomplete
            customerSearch.addEventListener('keyup', async function () {
                const query = this.value;
                if (query.length < 2) {
                    customerResults.innerHTML = '';
                    return;
                }

                try {
                    const response = await fetch(`/api/v1/customers/search?query=${query}`);
                    const customers = await response.json();
                    customerResults.innerHTML = customers.map(c => `<a href="#" class="list-group-item list-group-item-action" data-id="${c.id}" data-name="${c.display_name}">${c.display_name} (${c.id})</a>`).join('');
                } catch (error) {
                    console.error('Error searching customers:', error);
                }
            });

            customerResults.addEventListener('click', function (e) {
                e.preventDefault();
                if (e.target.matches('a')) {
                    customerId.value = e.target.dataset.id;
                    selectedCustomer.innerHTML = `<span class="badge bg-success">Selected: ${e.target.dataset.name}</span>`;
                    customerSearch.value = '';
                    customerResults.innerHTML = '';
                }
            });
        });

        function setDate(days) {
            const date = new Date();
            date.setDate(date.getDate() + days);
            document.getElementById('expiration_date').value = date.toISOString().split('T')[0];
        }
    </script>
    @endpush
</x-layout>
