<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityUserCompany extends Model
{
    protected $table = 'pbi_security_user_companies';

    protected $primaryKey = 'idusuario_empresa';

    public $timestamps = false;

    protected $fillable = [
        'idusuario',
        'idempresa',
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
     * @param  Builder<SecurityUserCompany>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('activo', true)->whereNull('fecha_eliminacion');
    }

    /**
     * @return BelongsTo<SecurityUser, $this>
     */
    public function securityUser(): BelongsTo
    {
        return $this->belongsTo(SecurityUser::class, 'idusuario', 'idusuario');
    }

    /**
     * @return BelongsTo<SecurityCompany, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(SecurityCompany::class, 'idempresa', 'idempresa');
    }
}
