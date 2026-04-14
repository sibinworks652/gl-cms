@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Attributes</h4>
                <div class="page-title-right">
                    <a href="{{ route('admin.ecommerce.attributes.create') }}" class="btn btn-primary">
                        <iconify-icon icon="mdi:plus" class="me-1"></iconify-icon> Add Attribute
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Options</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attributes as $attribute)
                            <tr>
                                <td><strong>{{ $attribute->name }}</strong></td>
                                <td>{{ $attribute->slug }}</td>
                                <td>
                                    @if($attribute->type === 'color')
                                    @foreach($attribute->options as $option)
                                       <span class="badge me-1" style="background-color: {{ $option->value }}; width: 23px; height: 23px; display: inline-block; border-radius: 50%; margin-left:-15px; border:2px solid #fff;"></span>
                                    @endforeach
                                    @else
                                     @foreach($attribute->options as $option)
                                        <span class="badge bg-secondary me-1">{{ $option->name }}</span>
                                    @endforeach
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $attribute->status ? 'success' : 'secondary' }}">
                                        {{ $attribute->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.ecommerce.attributes.edit', $attribute) }}" class="btn btn-soft-warning btn-sm">
                                     <iconify-icon icon="solar:pen-new-square-linear" width="16" height="16"></iconify-icon>
                                    </a>
                                    <form method="POST" action="{{ route('admin.ecommerce.attributes.destroy', $attribute) }}" class="d-inline" data-confirm="Delete this Attribute?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger" >
                                            <iconify-icon icon="solar:trash-bin-trash-linear" width="16" height="16"></iconify-icon>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No attributes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $attributes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
