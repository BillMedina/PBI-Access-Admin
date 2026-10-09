<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $table = 'pbi_menu_items';

    protected $primaryKey = 'idmenu';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'pagina_destino',
        'modulo',
        'orden',
        'activo',
        'requiere_permiso',
        'es_predeterminado',
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
            'requiere_permiso' => 'boolean',
            'es_predeterminado' => 'boolean',
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
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function general(Builder $query): void
    {
        $query->where('modulo', 'GENERAL');
    }

    /**
     * @return HasMany<MenuUserPermission, $this>
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(MenuUserPermission::class, 'idmenu', 'idmenu');
    }

    /**
     * @return HasMany<ProfileMenuItem, $this>
     */
    public function profileAssignments(): HasMany
    {
        return $this->hasMany(ProfileMenuItem::class, 'idmenu', 'idmenu');
    }

    public function isDefault(): bool
    {
        return $this->es_predeterminado || $this->getKey() === 0;
    }
}
