<?php

namespace Modules\Ecommerce\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Ecommerce\Models\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::latest()->paginate(20);
        return view('ecommerce::admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('ecommerce::admin.tags.form', [
            'tag' => new Tag([
                'type' => 'general',
                'status' => true,
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Tag::create($data);

        return redirect()->route('admin.ecommerce.tags.index')->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        return view('ecommerce::admin.tags.form', [
            'tag' => $tag,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $tag->update($data);

        return redirect()->route('admin.ecommerce.tags.index')->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return redirect()->route('admin.ecommerce.tags.index')->with('success', 'Tag deleted successfully.');
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $tags = Tag::where('name', 'like', "%{$search}%")
            ->where('status', true)
            ->limit(10)
            ->get();
        
        return response()->json($tags);
    }
}
