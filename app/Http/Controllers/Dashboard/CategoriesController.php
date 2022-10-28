<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $categories = Category::all();
        return view('dashboard.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $parents = Category::all();
        $category = new Category();
        return view('dashboard.categories.create', compact('category', 'parents'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
        return view('dashboard.categories.show', [
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        return view('dashboard.categories.edit', compact('category', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, $id)
    {
        //
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
            ->with('success', 'Category Updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $category = Category::findOrFail($id);
        $category->delete();
        return Redirect::route('dashboard.categories.index')
            ->with('danger', 'Category Deleted!');
    }
}
