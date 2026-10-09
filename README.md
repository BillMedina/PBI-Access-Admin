# PBI Access Admin

Aplicación Laravel independiente para administrar el menú general y las asignaciones directas por usuario de Power BI. No modifica datos funcionales de CPY.

## Alcance

- Alta, edición, baja lógica y auditoría de opciones del módulo `GENERAL`.
- Alta, edición y revocación lógica de excepciones correo → menú.
- ABM de perfiles RLS físicos, usuarios y empresas permitidas.
- Un perfil activo por usuario y una o varias empresas asignadas.
- Menús por perfil más excepciones individuales.
- Vistas MySQL de menú y seguridad para el modelo Power BI.
- Cuenta administrativa local protegida por una contraseña hasheada en variables de entorno.

La administración no modifica las reglas externas de equipos ni productos de Compras.

## Requisitos

- PHP 8.3+ con extensiones `pdo_mysql` y `mbstring`.
- Composer y Node.js.
- MySQL con permisos para crear las tres tablas y las dos vistas de esta aplicación.

## Instalación de la base MySQL

1. Si todavía no se instaló el menú, ejecute [01_install_pbi_menu_access.sql](database/sql/01_install_pbi_menu_access.sql) en el esquema `pbi`.
2. Ejecute [02_install_pbi_profile_security.sql](database/sql/02_install_pbi_profile_security.sql). Crea perfiles, usuarios, empresas, asignaciones, menús por perfil y las vistas de seguridad.
3. Cree una cuenta MySQL exclusiva para la aplicación con `SELECT`, `INSERT` y `UPDATE` únicamente sobre sus tablas `pbi_*`; el bloque de permisos opcional está al final del script.
4. Configure Power BI con una cuenta de solo lectura sobre las vistas indicadas abajo.

Los scripts no eliminan ni alteran tablas funcionales existentes. La segunda instalación reemplaza únicamente la vista propia `vw_pbi_menu_permisos` para incluir permisos por perfil y excepciones directas.

## Configuración local

1. Copie `.env.example` a `.env` si aún no existe.
2. Configure las variables `PBI_DB_*` para la conexión MySQL. No copie credenciales a archivos versionados.
3. Defina `PBI_ADMIN_USERNAME`.
4. Genere un hash, copie solo su salida a `PBI_ADMIN_PASSWORD_HASH` y mantenga la contraseña fuera del historial de comandos:

```powershell
php artisan pbi-access:make-password-hash
```

5. Ajuste `APP_URL` a la dirección local que se usará desde la red.
6. Instale los paquetes front-end y compile los estilos:

```powershell
npm install
npm run build
```

## Inicio en red local

Para pruebas internas, ejecute:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

Abra `http://IP_DEL_SERVIDOR:8000` desde un equipo autorizado de la misma red. Abra el puerto solo para la subred necesaria en el firewall de Windows. Para uso permanente, publique la carpeta `public` detrás de IIS, Apache o Nginx y active HTTPS.

## Administración de perfiles

La web administra una sola asignación de perfil por correo y una o más empresas. No permite combinar perfiles entre empresas para el mismo usuario.

| Perfil inicial | Rol físico que debe existir en el PBIX |
| --- | --- |
| Completo | `RLS_Completo` |
| Comercial | `RLS_Comercial` |
| Compras | `RLS_Compras` |
| Sin Tiendas1 | `RLS_SinTiendas1` |

Los perfiles futuros requieren tres acciones: crear sus reglas DAX en el PBIX, crear el rol publicado o grupo Entra ID correspondiente y registrarlo en la web con el mismo nombre.

La asignación de perfil en MySQL no mueve automáticamente una persona entre roles de Power BI Service. Mantenga cada persona en un único grupo o rol físico y refleje el mismo perfil en la web.

## Power BI

Conecte estas vistas MySQL al modelo mediante ODBC:

| Tabla del modelo | Vista MySQL |
| --- | --- |
| `Menu` | `vw_pbi_menu` |
| `Menu_Permisos` | `vw_pbi_menu_permisos` |
| `Seguridad_Usuarios` | `vw_pbi_security_users` |
| `Seguridad_Usuario_Empresa` | `vw_pbi_security_user_companies` |

La vista `vw_pbi_menu_permisos` suma permisos por perfil y excepciones individuales. Mantenga el filtro RLS de `Menu` en todos los perfiles:

```DAX
Menu[idmenu] = 0
    || Menu[idmenu]
        IN CALCULATETABLE(
            VALUES(Menu_Permisos[idmenu]),
            FILTER(
                Menu_Permisos,
                LOWER(Menu_Permisos[Correo]) = LOWER(USERPRINCIPALNAME())
            )
        )
```

En cada rol físico, aplique el siguiente patrón a `Master_Dim_Empresa`, reemplazando `COMPRAS` por el código del perfil correspondiente y `EmpresaClave` por la clave estable de la dimensión:

```DAX
VAR UsuarioActual = LOWER(USERPRINCIPALNAME())
VAR TienePerfil =
    CALCULATE(
        COUNTROWS(Seguridad_Usuarios),
        FILTER(
            ALL(Seguridad_Usuarios),
            Seguridad_Usuarios[Correo] = UsuarioActual
                && Seguridad_Usuarios[PerfilCodigo] = "COMPRAS"
        )
    ) > 0
VAR TieneEmpresa =
    CALCULATE(
        COUNTROWS(Seguridad_Usuario_Empresa),
        FILTER(
            ALL(Seguridad_Usuario_Empresa),
            Seguridad_Usuario_Empresa[Correo] = UsuarioActual
                && Seguridad_Usuario_Empresa[EmpresaClave]
                    = Master_Dim_Empresa[EmpresaClave]
        )
    ) > 0
RETURN
    TienePerfil && TieneEmpresa
```

Las reglas existentes de Compras para `PBI_Usuario_TEAM` y `Reglas_Producto` permanecen sin cambios dentro de `RLS_Compras`. Comercial, Completo y Sin Tiendas1 conservan también sus filtros particulares de clientes, ventas, compras, devoluciones, costo o lucro.

La medida `Destino_Menu` puede mantenerse:

```DAX
Destino_Menu = SELECTEDVALUE(Menu[pagina_destino], "MENU")
```

Si el modelo usa modo Import, los cambios aparecen después de actualizar el dataset. DirectQuery para estas cuatro vistas aplica cambios sin esperar la actualización del modelo, pero requiere gateway en Power BI Service.

## Validación

```powershell
php artisan test --compact
vendor/bin/pint --format agent
```
