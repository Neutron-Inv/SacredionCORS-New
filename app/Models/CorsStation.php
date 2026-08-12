<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CorsStation extends Model
{
    protected $fillable = [
        'ftp_account_id',
        'name',
        'slug',
        'code',
        'provider',
        'hero_text',
        'partner_logo_primary',
        'partner_logo_secondary',
        'location',
        'city',
        'state_name',
        'region',
        'latitude',
        'longitude',
        'map_embed_url',
        'ftp_file_prefix',
        'ftp_local_directory',
        'legacy_table',
        'is_external',
        'external_url',
        'is_active',
        'sort_order',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_external' => 'boolean',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function ftpAccount(): BelongsTo
    {
        return $this->belongsTo(FtpAccount::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CorsFile::class);
    }

    public function getPublicUrlAttribute(): string
    {
        if ($this->is_external && $this->external_url) {
            return $this->external_url;
        }

        return route('cors.show', $this->slug);
    }

    public function getHeroTextDisplayAttribute(): string
    {
        return $this->hero_text ?: "Welcome to {$this->name}";
    }

    public function getPartnerLogoPrimaryUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->partner_logo_primary);
    }

    public function getPartnerLogoSecondaryUrlAttribute(): ?string
    {
        return $this->resolveLogoUrl($this->partner_logo_secondary);
    }

    private function resolveLogoUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            return $value;
        }

        return asset(ltrim($value, '/'));
    }
}
