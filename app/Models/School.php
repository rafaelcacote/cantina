<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'document',
        'phone',
        'email',
        'address',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    public function dailyMenus(): HasMany
    {
        return $this->hasMany(DailyMenu::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Monta o endereço único a partir dos campos do formulário.
     * Formato: "Logradouro, Número - Bairro"
     */
    public static function composeAddress(?string $street, ?string $number, ?string $neighborhood): ?string
    {
        $street = trim((string) $street);
        $number = trim((string) $number);
        $neighborhood = trim((string) $neighborhood);

        if ($street === '' && $number === '' && $neighborhood === '') {
            return null;
        }

        $line = $street;

        if ($number !== '') {
            $line = $line !== '' ? "{$line}, {$number}" : $number;
        }

        if ($neighborhood !== '') {
            $line = $line !== '' ? "{$line} - {$neighborhood}" : $neighborhood;
        }

        return $line;
    }

    /**
     * Separa o endereço salvo no banco para o formulário.
     *
     * @return array{street: string, number: string, neighborhood: string}
     */
    public static function parseAddress(?string $address): array
    {
        $address = trim((string) $address);

        if ($address === '') {
            return [
                'street' => '',
                'number' => '',
                'neighborhood' => '',
            ];
        }

        $neighborhood = '';
        $remainder = $address;

        if (preg_match('/^(.*?)\s+-\s+(.+)$/u', $address, $matches)) {
            $remainder = trim($matches[1]);
            $neighborhood = trim($matches[2]);
        }

        $street = $remainder;
        $number = '';

        if (preg_match('/^(.*),\s*(.+)$/u', $remainder, $matches)) {
            $street = trim($matches[1]);
            $number = trim($matches[2]);
        }

        return [
            'street' => $street,
            'number' => $number,
            'neighborhood' => $neighborhood,
        ];
    }

    /**
     * @return array{street: string, number: string, neighborhood: string}
     */
    public function addressParts(): array
    {
        return self::parseAddress($this->address);
    }
}
