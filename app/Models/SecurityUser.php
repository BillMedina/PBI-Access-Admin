<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecurityUser extends Model
{
    protected $table = 'pbi_security_users';

    protected $primaryKey = 'idusuario';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'correo',
        'idperfil',
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
     * @param  Builder<SecurityUser>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('activo', true)->whereNull('fecha_eliminacion');
    }

    /**
     * @return BelongsTo<SecurityProfile, $this>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(SecurityProfile::class, 'idperfil', 'idperfil');
    }

    /**
     * @return HasMany<SecurityUserCompany, $this>
     */
    public function companyAssignments(): HasMany
    {
        return $this->hasMany(SecurityUserCompany::class, 'idusuario', 'idusuario');
    }
}
