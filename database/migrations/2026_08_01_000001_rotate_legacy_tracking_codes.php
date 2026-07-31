<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('detainees')
            ->select(['id', 'tracking_code'])
            ->orderBy('id')
            ->each(function (object $detainee): void {
                if ($detainee->tracking_code !== null && strlen($detainee->tracking_code) > 11) {
                    return;
                }

                DB::table('detainees')->where('id', $detainee->id)->update([
                    'tracking_code' => $this->generateUniqueCode(),
                ]);
            });
    }

    public function down(): void
    {
        // Secure codes are intentionally not downgraded or invalidated.
    }

    private function generateUniqueCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $suffix = '';
            for ($i = 0; $i < 12; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = 'TAYA-'.$suffix;
        } while (DB::table('detainees')->where('tracking_code', $code)->exists());

        return $code;
    }
};
