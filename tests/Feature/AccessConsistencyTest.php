<?php

namespace Tests\Feature;

use App\Helpers\Access;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Penjaga konsistensi: setiap nama izin literal yang dipakai di controller
 * (authorize/can) atau view (@can/@canany) wajib ada di matriks {@see Access}.
 * Typo izin akan langsung menggagalkan tes ini, bukan menjadi 403 senyap.
 *
 * Lookup dinamis (mis. `can($item['permission'])`) dilewati — namanya bukan literal.
 */
class AccessConsistencyTest extends TestCase
{
    private const DIRECTIVE = '/(?:@can|@elsecan|@canany|->can|->canAny|authorize)\(([^)]*)\)/';

    /** @return list<string> */
    private function permissionsIn(string $contents): array
    {
        preg_match_all(self::DIRECTIVE, $contents, $matches);

        $names = [];
        foreach ($matches[1] as $arguments) {
            if (str_contains($arguments, '$')) {
                continue; // lookup dinamis
            }
            preg_match_all("/'([^']+)'/", $arguments, $found);
            $names = [...$names, ...$found[1]];
        }

        return array_values(array_unique($names));
    }

    /** @return list<string> */
    private function sourceFiles(): array
    {
        return [
            ...File::allFiles(resource_path('views')),
            ...File::allFiles(app_path('Http/Controllers')),
        ];
    }

    public function test_every_permission_used_in_views_and_controllers_is_defined(): void
    {
        $catalogue = Access::allPermissions();
        $used = [];

        foreach ($this->sourceFiles() as $file) {
            foreach ($this->permissionsIn($file->getContents()) as $name) {
                $used[$name] = $file->getRelativePathname();
            }
        }

        $this->assertNotEmpty($used, 'Tidak ada izin terdeteksi — pola pencarian perlu diperbarui.');

        foreach ($used as $name => $file) {
            $this->assertContains(
                $name,
                $catalogue,
                "Izin '$name' dipakai di $file tetapi tidak ada di App\\Helpers\\Access."
            );
        }
    }

    public function test_defensive_clinical_permissions_are_actually_used(): void
    {
        $used = [];
        foreach ($this->sourceFiles() as $file) {
            $used = [...$used, ...$this->permissionsIn($file->getContents())];
        }
        $used = array_unique($used);

        $defensive = array_filter(
            Access::EXTRA_PERMISSIONS,
            fn (string $permission) => str_starts_with($permission, 'write ')
                || in_array($permission, ['record consent', 'revoke consent', 'upload visit attachment'], true)
        );

        foreach ($defensive as $permission) {
            $this->assertContains($permission, $used, "Izin '$permission' didefinisikan tapi tidak dipakai.");
        }
    }
}
