<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use MissingCredentialsException as GlobalMissingCredentialsException;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'serial_number', 'airwork_enabled'];

    protected function casts(): array
    {
        return ['airwork_enabled' => 'boolean'];
    }

    public function indeedCredentials(): HasOne
    {
        return $this->hasOne(CompanyIndeedCredential::class);
    }

    public function scrapingJobs(): HasMany
    {
        return $this->hasMany(ScrapingJob::class);
    }

    public function resolveIndeedLoginEmail(): string
    {
        if (filled($this->indeedCredentials?->email)) {
            return $this->indeedCredentials->email;
        }

        if (filled($this->serial_number)) {
            return str_replace('{serial_number}', $this->serial_number, config('scraping.indeed_email_template'));
        }

        throw new GlobalMissingCredentialsException("Company {$this->id} has no Indeed login email");
    }
}
