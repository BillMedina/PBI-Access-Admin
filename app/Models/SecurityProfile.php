<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityProfile extends Model
{
    protected $table = 'pbi_security_profiles';

    protected $primaryKey = 'idperfil';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'powerbi_role_name',
        'activo',
        'fecha_creacion',
        'usuario_creacion',
        'fecha_actualizacion',
        'usuario_actualizacion',
        'fecha_eliminacion',
        'usuario_eliminacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'fecha_creacion' => 'datetime',
            'fecha_actualizacion' => 'datetime',
            'fecha_eliminacion' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        $connection = config('access-control.connection');

        return is_string($connection) ? $connection : null;
    }

    /**
     * @param  Builder<SecurityProfile>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('activo', true)->whereNull('fecha_eliminacion');
    }

    /**
     * @return HasMany<SecurityUser, $this>
     */
    public function securityUsers(): HasMany
    {
        return $this->hasMany(SecurityUser::class, 'idperfil', 'idperfil');
    }

    /**
     * @return HasMany<ProfileMenuItem, $this>
     */
    public function menuAssignments(): HasMany
    {
        return $this->hasMany(ProfileMenuItem::class, 'idperfil', 'idperfil');
    }
}
