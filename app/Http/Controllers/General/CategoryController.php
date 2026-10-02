<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\General\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('read category');
        return view('pages.general.service.category.index', data: [
            'categoryList' => $this->service->readAllCategories(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $this->authorize('create category');
        $redirectRoute = isset($request->redirect) ? ['redirect' => $request->redirect] : null;

        return view('pages.general.service.category.form', [
            'type' => 'create',
            'data' => new Category,
            'back' => (object) $redirectRoute,
            'action' => route('categories.store', $redirectRoute),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(CategoryRequest $request)
    {
        $this->authorize('create category');
        $status = $this->service->createCategory($request->name, $request->code);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->withInput(['name' => $request->name, 'code' => $request->code])
                ->with('error', __('messages.service_category.error.oncreate'));
        }

        $redirect = isset($request->redirect) ? $request->redirect : 'categories';

        return redirect()->to($redirect)
            ->with('success', __('messages.service_category.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('read category');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(string $id)
    {
        $this->authorize('update category');
        return view('pages.general.service.category.form', [
            'type' => 'update',
            'data' => $this->service->readCategoryById($id),
            'action' => route('categories.update', $id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(CategoryRequest $request, $id)
    {
        $this->authorize('update category');
        $status = $this->service->updateCategory($id, $request->name, $request->code);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->withInput(['name' => $request->name])
                ->with('error', __('messages.service_category.error.onupdate'));
        }

        return redirect()->route('categories.index')
            ->with('success', __('messages.service_category.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(string $id)
    {
        $this->authorize('delete category');
        $status = $this->service->deleteCategory($id);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->with('error', __('messages.service_category.error.ondelete'));
        }

        return redirect()->route('categories.index')
            ->with('success', __('messages.service_category.success.ondelete'));
    }
}
