<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Attribute;
use Modules\Ecommerce\Models\AttributeOption;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('options')->latest()->paginate(20);
        return view('ecommerce::admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('ecommerce::admin.attributes.form',[
            'attribute' => new Attribute(['status' => true]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:select,color',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
            'option_values' => 'nullable|array',
            'option_values.*' => 'nullable|string|max:20',
            'status' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $attribute = Attribute::create($data);

        if (!empty($data['options'])) {
            foreach ($data['options'] as $index => $optionName) {
                if (!empty($optionName)) {
                    $attribute->options()->create([
                        'name' => $optionName,
                        'value' => $data['option_values'][$index] ?? null,
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.ecommerce.attributes.index')->with('success', 'Attribute created successfully.');
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('options');
        return view('ecommerce::admin.attributes.form', [
            'attribute' => $attribute,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:select,color',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
            'option_values' => 'nullable|array',
            'option_values.*' => 'nullable|string|max:20',
            'status' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $attribute->update($data);

        if (!empty($data['options'])) {
            $attribute->options()->delete();
            foreach ($data['options'] as $index => $optionName) {
                if (!empty($optionName)) {
                    $attribute->options()->create([
                        'name' => $optionName,
                        'value' => $data['option_values'][$index] ?? null,
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('admin.ecommerce.attributes.index')->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('admin.ecommerce.attributes.index')->with('success', 'Attribute deleted successfully.');
    }

    public function getOptions(Attribute $attribute)
    {
        $options = $attribute->options()->orderBy('order')->get();
        return response()->json($options);
    }
}
