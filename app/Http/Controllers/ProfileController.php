<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Models\Admin;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\User;
use App\Services\Master\AdminService;
use App\Services\Master\DoctorService;
use App\Services\Master\NurseService;
use App\Types\Entities\AdminEntity;
use App\Types\Entities\DoctorEntity;
use App\Types\Entities\NurseEntity;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    private $adminService;

    private $doctorService;

    private $nurseService;

    public function __construct()
    {
        $this->adminService = new AdminService;
        $this->doctorService = new DoctorService;
        $this->nurseService = new NurseService;
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        // Pengguna tanpa peran klinik (mis. akun umum) pakai halaman profil Breeze.
        if (! in_array($role, ['admin', 'manajemen', 'doctor', 'nurse'])) {
            return view('profile');
        }

        if ($role == 'admin' || $role == 'manajemen') {
            $data = $this->adminService->selectAdminByID($user->id);
        }

        if ($role == 'doctor') {
            $data = $this->doctorService->selectDoctorByID($user->id);
        }

        if ($role == 'nurse') {
            $data = $this->nurseService->selectNurseByID($user->id);
        }

        return view('pages.profile.index', [
            'data' => $data,
            'role' => $role,
            'action' => route('profile.update', $user->id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        if ($role == 'admin' || $role == 'manajemen') {
            $updatedAdmin = Admin::findOrFail($id);
            if ($request->hasFile('cover_image')) {
                if ($updatedAdmin->cover_picture) {
                    $this->adminService->deleteCoverImage($updatedAdmin->cover_picture);
                }
                $coverPicture = $this->adminService->storeCoverImage($request->cover_image);
            }

            if ($request->hasFile('profile_image')) {
                if ($updatedAdmin->profile_picture) {
                    $this->adminService->deleteProfileImage($updatedAdmin->profile_picture);
                }
                $profilePicture = $this->adminService->storeProfileImage($request->profile_image);
            }

            $admin = new AdminEntity;
            $admin->updateRequest(
                $request,
                $id,
                $profilePicture->path ?? $updatedAdmin->profile_picture,
                $coverPicture->path ?? $updatedAdmin->cover_picture
            );

            $data = $this->adminService->updateAdmin($admin, $user->id);
        }

        if ($role[0] == 'doctor') {
            $updatedDoctor = Doctor::findOrFail($id);
            if ($request->hasFile('cover_image')) {
                if ($updatedDoctor->cover_picture) {
                    $this->doctorService->deleteCoverImage($updatedDoctor->cover_picture);
                }
                $coverPicture = $this->doctorService->storeCoverImage($request->cover_image);
            }

            if ($request->hasFile('profile_image')) {
                if ($updatedDoctor->profile_picture) {
                    $this->doctorService->deleteProfileImage($updatedDoctor->profile_picture);
                }
                $profilePicture = $this->doctorService->storeProfileImage($request->profile_image);
            }

            $doctor = new DoctorEntity;
            $doctor->updateRequest(
                $request,
                $id,
                $profilePicture->path ?? $updatedDoctor->profile_picture,
                $coverPicture->path ?? $updatedDoctor->cover_picture
            );

            $data = $this->doctorService->updateDoctor($doctor, $user->id);
        }

        if ($role[0] == 'nurse') {
            $updatedNurse = Nurse::findOrFail($id);
            if ($request->hasFile('cover_image')) {
                if ($updatedNurse->cover_picture) {
                    $this->nurseService->deleteCoverImage($updatedNurse->cover_picture);
                }
                $coverPicture = $this->nurseService->storeCoverImage($request->cover_image);
            }

            if ($request->hasFile('profile_image')) {
                if ($updatedNurse->profile_picture) {
                    $this->nurseService->deleteProfileImage($updatedNurse->profile_picture);
                }
                $profilePicture = $this->nurseService->storeProfileImage($request->profile_image);
            }

            $nurse = new NurseEntity;
            $nurse->updateRequest(
                $request,
                $id,
                $profilePicture->path ?? $updatedNurse->profile_picture,
                $coverPicture->path ?? $updatedNurse->cover_picture
            );

            $data = $this->nurseService->updateNurse($nurse, $user->id);
        }

        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }

    public function changePassword()
    {
        return view('pages.profile.change-password', [
            'action' => route('profile.change-password.update', Auth::user()->id),
        ]);
    }

    public function updatePassword(PasswordRequest $request, $id)
    {
        $validated = $request->validated();
        $user = User::findOrFail($id);
        $password = Hash::make($validated['password']);
        if ($user->update(['password' => $password])) {
            return redirect()->route('profile.change-password')->with('success', 'Password updated successfully');
        }
    }

    public function switchLanguage($lang)
    {
        // App::setLocale($lang);
        // session(["my_locale", $lang]);
        Session::put('applocale', $lang);

        // dd(session());
        return redirect()->back();
    }
}
