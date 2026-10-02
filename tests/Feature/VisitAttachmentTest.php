<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Task 8 Fase 2: lampiran visit (private + signed URL).
 */
class VisitAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_nurse_can_upload_and_file_served_via_signed_url(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.attachments.store', $visit->id), [
            'type' => 'XRAY',
            'file' => UploadedFile::fake()->image('panoramik.jpg'),
            'description' => 'Panoramik awal',
        ])->assertRedirect();
        $attachment = $visit->attachments()->first();
        $this->assertNotNull($attachment);
        Storage::disk('local')->assertExists($attachment->path);

        $signed = URL::temporarySignedRoute('attachments.file', now()->addMinutes(30), ['attachment' => $attachment->id]);
        $this->actingAs($nurse)->get($signed)->assertOk();

        // Tanpa tanda tangan → 403.
        $this->actingAs($nurse)->get(route('attachments.file', $attachment->id))->assertForbidden();
    }

    public function test_upload_rejects_invalid_file(): void
    {
        $nurse = User::where('email', 'nurse@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($nurse)->post(route('visits.attachments.store', $visit->id), [
            'type' => 'BOGUS',
            'file' => UploadedFile::fake()->create('notes.txt', 10),
        ])->assertSessionHasErrors(['type', 'file']);
        $this->assertEquals(0, $visit->attachments()->count());
    }

    public function test_delete_removes_file_and_row(): void
    {
        $doctor = User::where('email', 'doctor@gmail.com')->first();
        $visit = Visit::factory()->create();

        $this->actingAs($doctor)->post(route('visits.attachments.store', $visit->id), [
            'type' => 'DOCUMENT',
            'file' => UploadedFile::fake()->create('rujukan.pdf', 100),
        ])->assertRedirect();
        $attachment = $visit->attachments()->first();

        $this->actingAs($doctor)
            ->delete(route('visits.attachments.destroy', [$visit->id, $attachment->id]))
            ->assertRedirect();
        Storage::disk('local')->assertMissing($attachment->path);
        $this->assertDatabaseMissing('visit_attachments', ['id' => $attachment->id]);
    }
}
