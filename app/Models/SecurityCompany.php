<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityCompany extends Model
{
    protected $table = 'pbi_security_companies';

    protected $primaryKey = 'idempresa';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'clave_pbi',
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
     * @param  Builder<SecurityCompany>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('activo', true)->whereNull('fecha_eliminacion');
    }

    /**
     * @return HasMany<SecurityUserCompany, $this>
     */
    public function userAssignments(): HasMany
    {
        return $this->hasMany(SecurityUserCompany::class, 'idempresa', 'idempresa');
    }
}
