<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAsset extends Model
{
    protected $fillable = [
        'key',
        'mime_type',
        'filename',
        'data_base64',
    ];

    /**
     * Returns the full data URI ready to embed in HTML/CSS.
     * e.g. "data:image/png;base64,iVBORw0..."
     */
    public function getDataUriAttribute(): string
    {
        return 'data:' . $this->mime_type . ';base64,' . $this->data_base64;
    }

    /**
     * Retrieve the logo asset or null.
     */
    public static function logo(): ?self
    {
        return static::where('key', 'logo')->first();
    }

    /**
     * Save or replace the logo from an uploaded file.
     */
    public static function storeLogo(\Illuminate\Http\UploadedFile $file): self
    {
        $base64 = base64_encode(file_get_contents($file->getRealPath()));

        return static::updateOrCreate(
            ['key' => 'logo'],
            [
                'mime_type'   => $file->getMimeType(),
                'filename'    => $file->getClientOriginalName(),
                'data_base64' => $base64,
            ]
        );
    }

    /**
     * Delete the logo.
     */
    public static function deleteLogo(): void
    {
        static::where('key', 'logo')->delete();
    }
}
