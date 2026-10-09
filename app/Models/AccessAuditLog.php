<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessAuditLog extends Model
{
    protected $table = 'pbi_access_audit_logs';

    protected $primaryKey = 'id_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'entidad',
        'entidad_id',
        'accion',
        'estado_anterior',
        'estado_posterior',
        'usuario_admin',
        'ip_address',
        'agente_usuario',
        'fecha_evento',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado_anterior' => 'array',
            'estado_posterior' => 'array',
            'fecha_evento' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        $connection = config('access-control.connection');

        return is_string($connection) ? $connection : null;
    }
}
