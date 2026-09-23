<?php

use App\Http\Controllers\Integration\PractitionerOnboardingController;
use App\Http\Controllers\Master\KfaController;
use App\Http\Controllers\Operational\OrganizationProfileController;
use App\Http\Controllers\Operational\RegionCodeController;
use App\Http\Controllers\Operational\SatuSehatCredentialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('api/regions/lookup', [RegionCodeController::class, 'lookup'])
        ->name('api.regions.lookup');

    // Fase 4.2: kamus KFA untuk autocomplete resep.
    Route::get('api/kfa/lookup', [KfaController::class, 'lookup'])->name('api.kfa.lookup');

    Route::get('satusehat/onboarding', [PractitionerOnboardingController::class, 'index'])
        ->name('satusehat.onboarding.index');
    Route::post('satusehat/onboarding/organization', [PractitionerOnboardingController::class, 'registerOrganization'])
        ->name('satusehat.onboarding.organization');
    Route::post('satusehat/onboarding/practitioner', [PractitionerOnboardingController::class, 'registerPractitioner'])
        ->name('satusehat.onboarding.practitioner');
    Route::post('satusehat/onboarding/location', [PractitionerOnboardingController::class, 'registerLocation'])
        ->name('satusehat.onboarding.location');

    Route::get('satusehat/organization-profiles', [OrganizationProfileController::class, 'index'])
        ->name('satusehat.org-profile.index');
    Route::post('satusehat/organization-profiles', [OrganizationProfileController::class, 'store'])
        ->name('satusehat.org-profile.store');
    Route::put('satusehat/organization-profiles/{id}', [OrganizationProfileController::class, 'update'])
        ->name('satusehat.org-profile.update');

    // Fase 2.1: kredensial per cabang.
    Route::get('satusehat/credentials', [SatuSehatCredentialController::class, 'index'])
        ->name('satusehat.credentials.index');
    Route::put('satusehat/credentials/{branch}', [SatuSehatCredentialController::class, 'update'])
        ->name('satusehat.credentials.update');
    Route::post('satusehat/credentials/{branch}/verify', [SatuSehatCredentialController::class, 'verify'])
        ->name('satusehat.credentials.verify');
});
