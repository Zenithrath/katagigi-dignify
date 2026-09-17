<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Nurse;
use App\Services\Master\NurseService;
use App\Types\Entities\NurseEntity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NurseController extends Controller
{
    private $service;

    public function __construct(NurseService $service)
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
        $this->authorize('read nurse');
        $nurseList = $this->service->selectAllNurse();

        return view('pages.master.nurse.index', ['nurseList' => $nurseList]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create nurse');

        return view('pages.master.nurse.form', [
            'type' => 'create',
            'action' => route('nurses.store'),
            'data' => new Nurse,
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
        $this->authorize('create nurse');
        $validated = $request->validated();
        $coverPicture = null;
        $profilePicture = null;

        if ($request->hasFile('cover_image')) {
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }

        $nurse = new NurseEntity;
        $nurse->fromRequest(
            $validated,
            Str::uuid(),
            $profilePicture->path ?? null,
            $coverPicture->path ?? null
        );
        $inserted = $this->service->insertNurse($nurse);

        if ($inserted instanceof Exception) {
            Log::error($inserted->getMessage());

            return back()
                ->withErrors('error', __('messages.nurse.error.oncreate'))->withInput();
        }

        return redirect()->route('nurses.index')
            ->with('success', __('messages.nurse.success.oncreate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = $this->service->selectNurseByID($id);

        return view('pages.master.nurse.form', [
            'type' => 'update',
            'action' => route('nurses.update', $id),
            'data' => $data,
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
        $updatedNurse = Nurse::findOrFail($id);
        if ($request->hasFile('cover_image')) {
            if ($updatedNurse->cover_picture) {
                $this->service->deleteCoverImage($updatedNurse->cover_picture);
            }
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            if ($updatedNurse->profile_picture) {
                $this->service->deleteProfileImage($updatedNurse->profile_picture);
            }
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }

        $nurse = new NurseEntity;
        $nurse->updateRequest(
            $request,
            $id,
            $profilePicture->path ?? $updatedNurse->profile_picture,
            $coverPicture->path ?? $updatedNurse->cover_picture
        );
        $updated = $this->service->updateNurse($nurse);
        if ($updated instanceof Exception) {
            Log::error($updated->getMessage());

            return back()
                ->withErrors('error', __('messages.nurse.error.onupdate'))->withInput();
        }

        return redirect()->route('nurses.index')
            ->with('success', __('messages.nurse.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $deletedNurse = Nurse::findOrFail($id);
        if ($deletedNurse->cover_picture) {
            $this->service->deleteCoverImage($deletedNurse->cover_picture);
        }
        if ($deletedNurse->profile_picture) {
            $this->service->deleteProfileImage($deletedNurse->profile_picture);
        }

        $deleted = $this->service->deleteNurse($id);
        if ($deleted instanceof Exception) {
            Log::error($deleted->getMessage());

            return back()
                ->withErrors('error', __('messages.nurse.error.ondelete'))->withInput();
        }

        return redirect()->route('nurses.index')
            ->with('success', __('messages.nurse.success.ondelete'));
    }
}
