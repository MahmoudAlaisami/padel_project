@extends('layouts.dashboard')

@section('title', 'Item Management')

@section('sidebar')
    <div class="sidebar-section">Admin Panel</div>
    <a href="{{ route('admin.dashboard') }}">&#128202; Dashboard</a>
    <a href="{{ route('admin.reservations') }}">&#128203; Reservations</a>
    <a href="{{ route('admin.items') }}" class="active">&#127907; Item Management</a>
    <a href="{{ route('admin.users') }}">&#128101; Users</a>
    <div class="sidebar-section">Account</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:.75rem;padding:.7rem 1rem;border-radius:.6rem;color:var(--text-muted);font-weight:500;transition:all .2s ease;background:none;border:none;cursor:pointer;width:100%;font-size:1rem;">
            &#128682; Sign Out
        </button>
    </form>
@endsection

@section('main')
    <h1 class="page-title">Item Management</h1>
    <p class="page-sub">Manage categories (Ball, Pitch, Racket) and their pricing types.</p>

    <div class="grid-2 mb-4">
        <!-- Add Category -->
        <div class="card">
            <h2 class="card-title">Add Category</h2>
            <form method="POST" action="{{ route('admin.items.add_category') }}">
                @csrf
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="category_name" class="form-control" placeholder="e.g. Ball" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>

        <!-- Add Item Type -->
        <div class="card">
            <h2 class="card-title">Add Item Type</h2>
            <form method="POST" action="{{ route('admin.items.add_type') }}">
                @csrf
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">— Select —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Type Name</label>
                    <input type="text" name="type_name" class="form-control" placeholder="e.g. Premium" required>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Type</button>
            </form>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--dark-3);display:flex;justify-content:space-between;align-items:center;">
            <h2 class="card-title" style="margin:0;">All Item Types</h2>
            <input type="text" id="itemSearch" class="form-control" placeholder="Search..." style="max-width:220px;">
        </div>
        <div class="table-wrap">
            <table id="itemsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Type Name</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($itemTypes as $it)
                    <tr>
                        <td>{{ $it->id }}</td>
                        <td>{{ $it->category?->name }}</td>
                        <td>{{ $it->type_name }}</td>
                        <td style="color:var(--green);font-weight:700;">${{ number_format($it->price, 2) }}</td>
                        <td>
                            <div class="flex gap-1">
                                <button class="btn btn-info btn-sm"
                                    data-modal-open="editModal"
                                    onclick="fillEditModal({{ $it->id }}, '{{ addslashes($it->type_name) }}', {{ $it->price }})">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.items.delete_type') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="type_id" value="{{ $it->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        data-confirm="Delete this item type? This may affect existing reservations.">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem;">No item types found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Categories list -->
    <div class="card mt-4" style="padding:0;overflow:hidden;">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--dark-3);">
            <h2 class="card-title" style="margin:0;">Categories</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Name</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>{{ $cat->id }}</td>
                        <td>{{ $cat->name }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.items.delete_category') }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="cat_id" value="{{ $cat->id }}">
                                <button type="submit" class="btn btn-danger btn-sm"
                                    data-confirm="Delete this category and ALL its item types? This cannot be undone.">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:2rem;">No categories found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

<!-- Edit Modal -->
@push('scripts')
<div class="modal-backdrop" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Item Type</span>
            <button class="modal-close" data-modal-close>&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.items.edit_type') }}">
            @csrf
            <input type="hidden" name="type_id" id="editTypeId">
            <div class="form-group">
                <label>Type Name</label>
                <input type="text" name="type_name" id="editTypeName" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" name="price" id="editTypePrice" class="form-control" step="0.01" min="0" required>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-outline" data-modal-close>Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function fillEditModal(id, name, price) {
    document.getElementById('editTypeId').value    = id;
    document.getElementById('editTypeName').value  = name;
    document.getElementById('editTypePrice').value = price;
}
document.addEventListener('DOMContentLoaded', () => tableSearch('itemSearch', 'itemsTable'));
</script>
@endpush
