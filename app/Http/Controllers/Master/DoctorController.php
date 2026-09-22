<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateDoctorRequest;
use App\Http\Requests\UserRequest;
use App\Models\Doctor;
use App\Services\Master\DoctorService;
use App\Types\Entities\DoctorEntity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    private $service;

    public function __construct(DoctorService $service)
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
        $this->authorize('read doctor');
        $doctorList = $this->service->selectAllDoctor();

        return view('pages.master.doctor.index', ['doctorList' => $doctorList]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create doctor');

        return view('pages.master.doctor.form', [
            'type' => 'create',
            'action' => route('doctors.store'),
            'data' => new Doctor,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(UserRequest $request)
    {
        $this->authorize('create doctor');
        $validated = $request->validated();
        $coverPicture = null;
        $profilePicture = null;

        if ($request->hasFile('cover_image')) {
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }

        $doctor = new DoctorEntity;
        $doctor->fromRequest(
            $validated,
            Str::uuid(),
            $profilePicture->path ?? null,
            $coverPicture->path ?? null
        );
        $inserted = $this->service->insertDoctor($doctor);

        if ($inserted instanceof Exception) {
            Log::error($inserted->getMessage());

            return back()
                ->withErrors(['error' => __('messages.doctor.error.oncreate')])->withInput();
        }

        return redirect()->route('doctors.index')
            ->with('success', __('messages.doctor.success.oncreate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $this->authorize('update doctor');
        $data = $this->service->selectDoctorByID($id);

        return view('pages.master.doctor.form', [
            'type' => 'update',
            'action' => route('doctors.update', $id),
            'data' => $data,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(UpdateDoctorRequest $request, $id)
    {
        $this->authorize('update doctor');
        $updatedDoctor = Doctor::findOrFail($id);
        $validated = $request->validated();
        if ($request->hasFile('cover_image')) {
            if ($updatedDoctor->cover_picture) {
                $this->service->deleteCoverImage($updatedDoctor->cover_picture);
            }
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            if ($updatedDoctor->profile_picture) {
                $this->service->deleteProfileImage($updatedDoctor->profile_picture);
            }
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }

        $doctor = new DoctorEntity;
        $doctor->updateRequest(
            $validated,
            $id,
            $profilePicture->path ?? $updatedDoctor->profile_picture,
            $coverPicture->path ?? $updatedDoctor->cover_picture
        );
        $updated = $this->service->updateDoctor($doctor);
        if ($updated instanceof Exception) {
            Log::error($updated->getMessage());

            return back()
                ->withErrors(['error' => __('messages.doctor.error.onupdate')])->withInput();
        }

        return redirect()->route('doctors.index')
            ->with('success', __('messages.doctor.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->authorize('delete doctor');
        $deletedDoctor = Doctor::findOrFail($id);
        if ($deletedDoctor->cover_picture) {
            $this->service->deleteCoverImage($deletedDoctor->cover_picture);
        }
        if ($deletedDoctor->profile_picture) {
            $this->service->deleteProfileImage($deletedDoctor->profile_picture);
        }

        $deleted = $this->service->deleteDoctor($id);
        if ($deleted instanceof Exception) {
            Log::error($deleted->getMessage());

            return back()
                ->withErrors(['error' => __('messages.doctor.error.ondelete')])->withInput();
        }

        return redirect()->route('doctors.index')
            ->with('success', __('messages.doctor.success.ondelete'));
    }
}
