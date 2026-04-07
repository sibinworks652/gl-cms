<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->name }}</title>
    <style>
        body { margin: 0; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #0f172a; }
        .shell { max-width: 860px; margin: 0 auto; padding: 3rem 1.5rem; }
        .card { background: #fff; border-radius: 24px; padding: 2rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .grid { display: grid; gap: 1rem; }
        label { display: block; font-weight: 600; margin-bottom: 0.45rem; }
        input, textarea, select { width: 100%; padding: 0.85rem 1rem; border: 1px solid #dbe2ea; border-radius: 12px; font: inherit; }
        .choice { display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem; }
        .choice input { width: auto; }
        button { padding: 0.9rem 1.2rem; border: 0; border-radius: 999px; background: #2563eb; color: #fff; font-weight: 600; cursor: pointer; }
        .muted { color: #64748b; }
        .alert { margin: 0 0 1rem; padding: 0.9rem 1rem; border-radius: 12px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .error-text { color: #b91c1c; font-size: 0.875rem; margin-top: 0.35rem; }
    </style>
</head>
<body>
    <div class="shell">
        <div class="card">
            <h1>{{ $form->name }}</h1>
            @if($form->description)
                <p class="muted">{{ $form->description }}</p>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form class="grid" method="POST" action="{{ route('forms.public.submit', $form->slug) }}">
                @csrf
                @forelse ($fields as $field)
                    <div>
                        <label>{{ $field['label'] }} @if(!empty($field['required']))* @endif</label>

                        @if(in_array($field['type'], ['text', 'email', 'number', 'date']))
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" value="{{ old($field['name']) }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if(!empty($field['required'])) required @endif>
                        @elseif($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}" rows="4" placeholder="{{ $field['placeholder'] ?? '' }}" @if(!empty($field['required'])) required @endif>{{ old($field['name']) }}</textarea>
                        @elseif($field['type'] === 'select')
                            <select name="{{ $field['name'] }}" @if(!empty($field['required'])) required @endif>
                                <option value="">Select an option</option>
                                @foreach(($field['options'] ?? []) as $option)
                                    <option value="{{ $option }}" @selected(old($field['name']) == $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'checkbox')
                            @php($selectedValues = (array) old($field['name'], []))
                            @foreach(($field['options'] ?? []) as $option)
                                <label class="choice"><input type="checkbox" name="{{ $field['name'] }}[]" value="{{ $option }}" @checked(in_array($option, $selectedValues, true))> <span>{{ $option }}</span></label>
                            @endforeach
                        @elseif($field['type'] === 'radio')
                            @foreach(($field['options'] ?? []) as $option)
                                <label class="choice"><input type="radio" name="{{ $field['name'] }}" value="{{ $option }}" @checked(old($field['name']) == $option) @if(!empty($field['required'])) required @endif> <span>{{ $option }}</span></label>
                            @endforeach
                        @endif

                        @error($field['name'])
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                        @error($field['name'] . '.*')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>
                @empty
                    <p class="muted">No fields configured for this form yet.</p>
                @endforelse

                @if(!empty($fields))
                    <div><button type="submit">Submit</button></div>
                @endif
            </form>
        </div>
    </div>
</body>
</html>
