<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileMenuItem extends Model
{
    protected $table = 'pbi_security_profile_menu_items';

    protected $primaryKey = 'idperfil_menu';

    public $timestamps = false;

    protected $fillable = [
        'idperfil',
        'idmenu',
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
     * @param  Builder<ProfileMenuItem>  $query
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
     * @return BelongsTo<MenuItem, $this>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'idmenu', 'idmenu');
    }
}
