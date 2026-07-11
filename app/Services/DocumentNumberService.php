<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate the next document number for a given prefix.
     *
     * Format: {PREFIX}{YYYYMMDD}-{N}
     * Example: INV-20260711-3
     *
     * The counter is per prefix per calendar day and is incremented
     * inside a transaction with a row-level lock so concurrent requests
     * never produce duplicate numbers.
     *
     * @param  string $prefix  e.g. "INV-", "Q-", "FACT-", "DEV-"
     * @return string          e.g. "INV-20260711-3"
     */
    public function generate(string $prefix): string
    {
        $today = now()->format('Y-m-d');

        $counter = DB::transaction(function () use ($prefix, $today) {

            // Find or create the row for this prefix + today, then lock it
            $row = DB::table('document_counters')
                ->where('prefix', $prefix)
                ->where('date', $today)
                ->lockForUpdate()
                ->first();

            if ($row) {
                $next = $row->counter + 1;
                DB::table('document_counters')
                    ->where('prefix', $prefix)
                    ->where('date', $today)
                    ->update(['counter' => $next, 'updated_at' => now()]);
            } else {
                $next = 1;
                DB::table('document_counters')->insert([
                    'prefix'     => $prefix,
                    'date'       => $today,
                    'counter'    => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $next;
        });

        $datePart = now()->format('Ymd');   // 20260711

        return $prefix . $datePart . '-' . $counter;
    }
}
