<?php

namespace App\Http\Controllers\General;

use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\General\ServiceRequest;
use App\Models\Service;
use App\Services\General\ServiceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    private $service;

    public function __construct(ServiceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->authorize('read service');
        $rupiahConverter = function (float $value) {
            return GeneralHelper::floatToRupiah($value);
        };
        $total = $this->service->countData($request);
        $limit = $request->limit ?? 20;
        $pagination = (object) [
            'page' => $request->page == 0 ? 1 : (int) $request->page,
            'limit' => (int) $limit,
            'last' => (int) ceil($total / $limit),
            'total' => (int) $total,
        ];

        return view('pages.general.service.index', [
            'serviceList' => $this->service->readAllServices($request),
            'pagination' => $pagination,
            'toRupiah' => $rupiahConverter,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create service');
        return view('pages.general.service.form', [
            'type' => 'create',
            'data' => new Service,
            'action' => route('services.store'),
            'categories' => $this->service->readAllCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(ServiceRequest $request)
    {
        $this->authorize('create service');
        $category_code = $this->service->readCategoryByID($request->category_id)->code;
        $service_code = $this->service->generateServiceCode($category_code);

        $status = $this->service->createService($request, $service_code);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.service.error.oncreate'));
        }

        $redirect = isset($request->redirect) ? $request->redirect : 'services';

        return redirect()->to($redirect)
            ->with('success', __('messages.service.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $this->authorize('read service');
        $rupiahConverter = function (float $amount) {
            return GeneralHelper::floatToRupiah($amount);
        };

        return view('pages.general.service.detail', [
            'data' => $this->service->readServiceByID($id),
            'toRupiah' => $rupiahConverter,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->authorize('update service');
        return view('pages.general.service.form', [
            'type' => 'update',
            'data' => $this->service->readServiceByID($id),
            'action' => route('services.update', $id),
            'categories' => $this->service->readAllCategories(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update service');
        $old_service = $this->service->readServiceByID($id);
        $old_category_id = $old_service->category_id;
        $code = $old_service->code;

        if ($old_service->category_id != $request->category_id) {
            $category_code = $this->service->readCategoryByID($request->category_id)->code;
            $code = $this->service->generateServiceCode($category_code);
        }

        $status = $this->service->updateService($request, $code, $id);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->withInput()
                ->with('error', __('messages.service.error.onupdate'));
        }

        $redirect = isset($request->redirect) ? $request->redirect : 'services';

        return redirect()->to($redirect)
            ->with('success', __('messages.service.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete service');
        $status = $this->service->deleteService($id);

        if ($status instanceof Exception) {
            return redirect()->back()
                ->with('error', __('messages.service.error.ondelete'));
        }

        return redirect()->route('services.index')
            ->with('success', __('messages.service.success.ondelete'));
    }
}
