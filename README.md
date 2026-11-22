# Sistema de Compras y Contrataciones — API

API del Caso B (Fase 4) de la Cooperativa Guayacán: proveedores, solicitudes de compra, órdenes, recepciones, contratos y reportes.

El front-end está en el repositorio **compras-front**.

---

## Requisitos

| Herramienta | Versión |
|---|---|
| Laragon | Versión completa (incluye Nginx y Composer) |
| PHP | 8.4 (NTS, x64) |
| Composer | 2.x |
| SQL Server | 2022 (o Express) |
| Microsoft ODBC Driver for SQL Server | 17 |
| Drivers de PHP para SQL Server | `sqlsrv` y `pdo_sqlsrv` para PHP 8.4 |

---

## Pasos de instalación

### 1. Habilitar SQL Server en PHP
1. Instalar el **ODBC Driver 17 for SQL Server**.
2. Descargar los **Microsoft Drivers for PHP for SQL Server** y copiar estos archivos en `C:\laragon\bin\php\php-8.4.x\ext`:
    - `php_sqlsrv_84_nts_x64.dll`
    - `php_pdo_sqlsrv_84_nts_x64.dll`
3. En Laragon: **Menú → PHP → Extensiones** → activar `sqlsrv` y `pdo_sqlsrv`.
4. Reiniciar Laragon y comprobar en la terminal:
   ```bash
   php -m
   ```
   Deben aparecer `sqlsrv` y `pdo_sqlsrv`.

### 2. Clonar el proyecto
En la terminal de Laragon (**Menú → Terminal**):
```bash
cd C:\laragon\www
git clone https://github.com/CASTILLOAXEL/compras-back.git
cd compras-back
composer install
```

### 3. Crear la base de datos
En SQL Server:
```sql
CREATE DATABASE dbcompras;
```

### 4. Configurar el `.env`
```bash
copy .env.example .env
php artisan key:generate
```

El `.env.example` ya trae la configuración del proyecto. En el `.env` solo escribir la contraseña de SQL Server (y el usuario, si no es `sa`):
```env
DB_USERNAME=sa
DB_PASSWORD=su_contraseña
```

### 5. Ejecutar las migraciones
Crea las tablas:
```bash
php artisan migrate
```

### 6. Ejecutar los seeders
Carga los catálogos, los usuarios de prueba y los datos de ejemplo:
```bash
php artisan db:seed
```

### 7. Levantar el sitio
En Laragon presionar **Start All**. La API queda en:
```
http://compras-back.local
```

### 8. Comprobar
Abrir `http://compras-back.local/api/paises` en el navegador. Debe responder **401 (Unauthenticated)**, porque la API pide iniciar sesión.

---

## Reiniciar la base de datos

Borra todas las tablas y las vuelve a crear con sus datos:
```bash
php artisan migrate:fresh --seed
```

---

## Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Super Administrador | `admin@compras.test` | `Gy202600` |
| Solicitante | `solicitante@compras.test` | `Gy202600` |
