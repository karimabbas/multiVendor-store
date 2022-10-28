@extends('layouts.dashboard')

@section('title', 'Categories')

@section('breadcrumb')
    @parent
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')

    <div class="mb-5">
        {{-- @if (Auth::user()->can('categories.create')) --}}
            <a href="{{ route('dashboard.categories.create') }}" class="btn btn-bg btn-outline-success mr-2">Create</a>
        {{-- @endif --}}
        {{-- <a href="{{ route('dashboard.categories.trash') }}" class="btn btn-sm btn-outline-dark">Trash</a> --}}
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success" id=successMessage>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('info'))
        <div class="alert alert-info" id=successMessage>
            {{ session('info') }}
        </div>
    @endif
    @if (session()->has('danger'))
        <div class="alert alert-danger" id=successMessage>
            {{ session('danger') }}
        </div>
    @endif
    <script>
        setTimeout(function() {
            $('#successMessage').fadeOut('fast');
        }, 3000);
    </script>


    <form action="{{ URL::current() }}" method="get" class="d-flex justify-content-between mb-4">
        <input name="name" placeholder="Name" class="mx-2" :value="request('name')" />
        <select name="status" class="form-control mx-2">
            <option value="">All</option>
            <option value="active" @selected(request('status') == 'active')>Active</option>
            <option value="archived" @selected(request('status') == 'archived')>Archived</option>
        </select>
        <button class="btn btn-dark mx-2">Filter</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Parent</th>
                <th>Created At</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td><img src="{{ asset($category->image) }}" alt="" height="50" width="100"></td>
                    <td><a href="{{ route('dashboard.categories.show', $category->id) }}">{{ $category->name }}</a></td>
                    <td>{{ $category->parent_id }}</td>
                    <td>{{ $category->created_at }}</td>
                    <td>
                        {{-- @can('categories.update') --}}
                        <a href="{{ route('dashboard.categories.edit', $category->id) }}"
                            class="btn btn-sm btn-outline-warning">Edit</a>
                        {{-- @endcan --}}
                    </td>
                    <td>
                        {{-- @can('categories.delete') --}}
                        <form action="{{ route('dashboard.categories.destroy', $category->id) }}" method="post">
                            @csrf
                            <!-- Form Method Spoofing -->
                            <input type="hidden" name="_method" value="delete">
                            @method('delete')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        {{-- @endcan --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">No categories defined.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- {{ $categories->withQueryString()->appends(['search' => 1])->links() }} --}}

@endsection
