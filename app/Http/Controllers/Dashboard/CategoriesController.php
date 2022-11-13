<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CategoriesController extends Controller
{

    public function index(Request $request)
    {

        if (!Gate::allows('categories.view')) {
            abort(403);
        }
        // $query = Category::query();
        // if ($name = $request->name) {
        //     $query->where('name', 'LIKE', "%{$name}%");
        // }
        // if ($status = $request->status) {
        //     $query->where('status', '=', $status);
        // }
        // $categories = $query->paginate(4);
 
        // SELECT a.*, b.name as parent_name 
        // FROM categories as a
        // LEFT JOIN categories as b ON b.id = a.parent_id

        // ->withCount([
        //     'products as products_number' => function($query) {
        //     $query->where('status', 'active');
        //     }
        //     ])
        $categories = Category::with('parent')->withCount('products as products_count')
            // ::leftJoin('categories as parents', 'parents.id', '=', 'categories.parent_id')
            // ->select(['categories.*', 'parents.name as parent_name'])
            ->filter($request->query())
            ->latest()
            ->paginate(4);
        return view('dashboard.categories.index', compact('categories'));
    }


    public function create()
    {
        // if (Gate::denies('categories.create')) {
        //     abort(403);
        // }
        $parents = Category::all();
        $category = new Category();
        return view('dashboard.categories.create', compact('category', 'parents'));
    }

    public function upload_image($file, $prefix)
    {

        if ($file) {
            // $files = $file;
            $imageName = $prefix . rand(3, 999) . '-' . time() . '.' . $file->extension();
            $image = "storage/Category/" . $imageName;
            $file->move(public_path('storage/Category'), $imageName);
            $getValue = $image;

            return $getValue;
        }
    }
    public function store(CategoryRequest $request)
    {
        // Gate::authorize('categories.create');
        // Ways to store data from requset(single and array values)
        // $request->input('name');
        // $request->post('name');
        // $request->query('name'); 
        // $request->get('name');
        // $request->name;
        // $request['name'];

        // $request->all(); // returne array of all input
        // $request->only(['name', 'parent_id']);
        // $request->except(['image', 'status']);

        // $request->validate(Category::rules(),[
        // ]);

        $request->merge([
            'slug' => Str::slug($request->post('name'))
        ]);

        $data = $request->except('image');
        $path = $this->upload_image($request->file('image'), 'category_');
        $data['image'] = $path;
        // if ($request->file('image')) {
        //     $path = $this->upload_image($request->file('image'), 'Equipment_');
        // } else if ($request->file('image') === null) {
        //     $path = $equpment->image;
        // }

        // $data = $request->except('image');
        // $data['image'] = $this->uploadImgae($request);


        // Mass assignment
        $category = Category::create($data);

        // PRG
        return Redirect::route('dashboard.categories.index')
            ->with('success', 'Category created!');
    }


    public function show(Category $category)
    {
        // if (!Gate::allows('categories.view')) {
        //     abort(403);
        // }

        return view('dashboard.categories.show', [
            'category' => $category
        ]);
    }

    public function edit($id)
    {
        try {
            $category = Category::findOrFail($id);
        } catch (Exception $e) {
            return redirect()->route('dashboard.categories.index')->with('info', 'Record not found');
        }
        $parents = Category::where('id', '<>', $id)
            ->where(function ($equery) use ($id) {
                $equery->whereNull('parent_id')
                    ->orWhere('parent_id', '<>', $id);
            })
            ->get();
        // dd($parents);
        return view('dashboard.categories.edit', compact('category', 'parents'));
    }

    public function update(CategoryRequest $request, $id)
    {
        // Gate::authorize('categories.update');

        $request->validate(Category::rules($id));
        $category = Category::find($id);

        $old_image = $category->image;

        $data = $request->except('image');
        $path = $this->upload_image($request->file('image'), 'category_');
        if ($path) {
            $data['image'] = $path;
        }

        $data['name'] = $request->name;
        $data['parent_id'] = $request->parent_id;
        $category->update($data);

        if ($old_image && $path) {
            Storage::disk('public')->delete($old_image);
        }
        // $category->update($request->all());

        return Redirect::route('dashboard.categories.index')
            ->with('warning', 'Category Updated!');
    }

    public function destroy(Category $category)
    {
        //
        // $category = Category::findOrFail($id);
        $category->delete();
        return Redirect::route('dashboard.categories.index')
            ->with('danger', 'Category Deleted!');
    }

    public function trash()
    {
        $categories = Category::onlyTrashed()->paginate(4);

        return view('dashboard.categories.trash', compact('categories'));
    }

    public function restore(Request $request, $id)
    {

        $category = Category::onlyTrashed()->findOrFail($id)->restore();

        return Redirect::route('dashboard.categories.trash')
            ->with('success', 'Category restored successfullay!');
    }

    public function forceDelete($id)
    {

        $category = Category::onlyTrashed()->findOrFail($id)->forceDelete();

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        return Redirect::route('dashboard.categories.trash')
            ->with('success', 'Category deleted permantally!');
    }
}
