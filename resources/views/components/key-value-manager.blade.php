@props(['label', 'name', 'items' => []])

<div class="mb-3">
    <label class="form-label">{{ $label }}</label>
    <div id="{{ $name }}-container">
        @foreach($items as $index => $item)
            <div class="input-group mb-2">
                <input type="hidden" name="{{ $name }}[{{ $index }}][id]" value="{{ $item->id }}">
                <input type="text" class="form-control" name="{{ $name }}[{{ $index }}][key]" value="{{ $item->key }}" placeholder="Key" required>
                <input type="text" class="form-control" name="{{ $name }}[{{ $index }}][data]" value="{{ $item->data }}" placeholder="Value" required>
                <button type="button" class="btn btn-danger remove-row">Remove</button>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-secondary btn-sm" id="add-{{ $name }}">Add {{ Str::singular($label) }}</button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let container = document.getElementById('{{ $name }}-container');
        let addButton = document.getElementById('add-{{ $name }}');
        let index = {{ count($items) }};

        addButton.addEventListener('click', function () {
            let row = document.createElement('div');
            row.className = 'input-group mb-2';
            row.innerHTML = `
                <input type="text" class="form-control" name="{{ $name }}[${index}][key]" placeholder="Key" required>
                <input type="text" class="form-control" name="{{ $name }}[${index}][data]" placeholder="Value" required>
                <button type="button" class="btn btn-danger remove-row">Remove</button>
            `;
            container.appendChild(row);
            index++;
        });

        container.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.parentElement.remove();
            }
        });
    });
</script>
