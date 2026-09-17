<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Admin;
use App\Services\Master\AdminService;
use App\Types\Entities\AdminEntity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class AdminController extends Controller
{
    private $service;

    public function __construct(AdminService $service)
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
        $this->authorize('read admin');
        $adminList = $this->service->selectAllAdmin();

        return view('pages.master.admin.index', ['adminList' => $adminList]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $this->authorize('create admin');

        return view('pages.master.admin.form', [
            'type' => 'create',
            'action' => route('admins.store'),
            'data' => new Admin,
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
        $this->authorize('create admin');
        $validated = $request->validated();
        $coverPicture = null;
        $profilePicture = null;

        if ($request->hasFile('cover_image')) {
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }

        $admin = new AdminEntity;
        $admin->fromRequest(
            $validated,
            Str::uuid(),
            $profilePicture->path ?? null,
            $coverPicture->path ?? null
        );
        $inserted = $this->service->insertAdmin($admin);

        if ($inserted instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($inserted->getMessage());

            return back()
                ->withErrors('error', __('messages.admin.error.oncreate'))->withInput();
        }

        return redirect()->route('admins.index')
            ->with('success', __('messages.admin.success.oncreate'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data = $this->service->selectAdminByID($id);

        return view('pages.master.admin.form', [
            'type' => 'update',
            'action' => route('admins.update', $id),
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
        $updatedAdmin = Admin::findOrFail($id);
        if ($request->hasFile('cover_image')) {
            if ($updatedAdmin->cover_picture) {
                $this->service->deleteCoverImage($updatedAdmin->cover_picture);
            }
            $coverPicture = $this->service->storeCoverImage($request->cover_image);
        }

        if ($request->hasFile('profile_image')) {
            if ($updatedAdmin->profile_picture) {
                $this->service->deleteProfileImage($updatedAdmin->profile_picture);
            }
            $profilePicture = $this->service->storeProfileImage($request->profile_image);
        }
        $admin = new AdminEntity;
        $admin->updateRequest(
            $request,
            $id,
            $profilePicture->path ?? $updatedAdmin->profile_picture,
            $coverPicture->path ?? $updatedAdmin->cover_picture
        );
        $updated = $this->service->updateAdmin($admin);
        if ($updated instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($updated->getMessage());

            return back()
                ->withErrors('error', __('messages.admin.error.onupdate'))->withInput();
        }

        return redirect()->route('admins.index')
            ->with('success', __('messages.admin.success.onupdate'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $deletedAdmin = Admin::findOrFail($id);
        if ($deletedAdmin->cover_picture) {
            $this->service->deleteCoverImage($deletedAdmin->cover_picture);
        }
        if ($deletedAdmin->profile_picture) {
            $this->service->deleteProfileImage($deletedAdmin->profile_picture);
        }

        $deleted = $this->service->deleteAdmin($id);
        if ($deleted instanceof Exception) {
            $output = new ConsoleOutput;
            $output->writeln($deleted->getMessage());

            return back()
                ->withErrors('error', __('messages.admin.error.ondelete'))->withInput();
        }

        return redirect()->route('admins.index')
            ->with('success', __('messages.admin.success.ondelete'));
    }
}
