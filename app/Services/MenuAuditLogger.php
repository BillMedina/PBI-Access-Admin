<?php

namespace App\Services;

use App\Models\AccessAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuAuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     */
    public function record(string $action, Model $model, Request $request, ?array $before = null): void
    {
        AccessAuditLog::query()->create([
            'entidad' => $model->getTable(),
            'entidad_id' => (string) $model->getKey(),
            'accion' => $action,
            'estado_anterior' => $before,
            'estado_posterior' => $model->getAttributes(),
            'usuario_admin' => (string) $request->session()->get('pbi_admin_username'),
            'ip_address' => $request->ip(),
            'agente_usuario' => Str::limit((string) $request->userAgent(), 255, ''),
            'fecha_evento' => now(),
        ]);
    }
}
