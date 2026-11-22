<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Genera el CRUD de una tabla
 * modelo, controlador, requests de creación y edición, seeder de permisos y ruta.
 * Al final imprime el JSON que usa el generador del front (make:crud).
 *
 * El comando no sobrescribe archivos existentes a menos que el usuario confirme.
 */
class GenerarCrudCommand extends Command
{
    protected $signature = 'generar:crud
                            {tabla : Nombre de la tabla (ej. proveedores)}
                            {modelo : Nombre del modelo en singular (ej. Proveedor)}';

    protected $description = 'Genera modelo, controlador, requests, seeder de permisos y ruta de una tabla';

    // Columnas que maneja Laravel; no van en formularios ni validaciones
    private const COLUMNAS_AUTOMATICAS = ['id', 'created_at', 'updated_at', 'deleted_at'];

    // Marcas donde el comando inserta la ruta y el seeder generados
    private const MARCA_RUTAS = '// generar:crud rutas';
    private const MARCA_SEEDERS = '// generar:crud permisos';

    private string $tabla;
    private string $modelo;
    private string $variable;
    private string $variablePlural;
    private string $nombrePermiso;
    private array $columnas = [];
    private array $llavesForaneas = [];
    private array $columnasUnicas = [];
    private bool $tieneSoftDeletes = false;

    public function handle(): int
    {
        $this->tabla = $this->argument('tabla');
        $this->modelo = Str::studly($this->argument('modelo'));
        $this->variable = Str::camel($this->modelo);
        $this->variablePlural = Str::camel($this->tabla);
        $this->nombrePermiso = $this->camelCaseAFraseMinusculaConPlural($this->modelo);

        if (! Schema::hasTable($this->tabla)) {
            $this->error("La tabla '{$this->tabla}' no existe.");

            return self::FAILURE;
        }

        $rutaModelo = app_path("Models/{$this->modelo}.php");

        if (File::exists($rutaModelo) && ! $this->confirm("El modelo {$this->modelo} ya existe. ¿Sobrescribir sus archivos?")) {
            $this->warn('Generación cancelada.');

            return self::SUCCESS;
        }

        $this->leerEsquema();
        $this->generarArchivos();
        $this->agregarRuta();
        $this->agregarSeederPermisos();
        $this->mostrarJsonParaFront();

        return self::SUCCESS;
    }


    private function leerEsquema(): void
    {
        $this->columnas = array_values(array_filter(
            Schema::getColumns($this->tabla),
            fn (array $columna) => ! in_array($columna['name'], self::COLUMNAS_AUTOMATICAS)
        ));

        $this->llavesForaneas = collect(Schema::getForeignKeys($this->tabla))
            ->mapWithKeys(fn (array $llave) => [$llave['columns'][0] => $llave['foreign_table']])
            ->all();

        $this->columnasUnicas = collect(Schema::getIndexes($this->tabla))
            ->filter(fn (array $indice) => $indice['unique'] && ! $indice['primary'] && count($indice['columns']) === 1)
            ->map(fn (array $indice) => $indice['columns'][0])
            ->values()
            ->all();

        $this->tieneSoftDeletes = Schema::hasColumn($this->tabla, 'deleted_at');
    }

    private function generarArchivos(): void
    {
        $this->info(' Archivos generados:');

        $reemplazos = [
            '{{ modelo }}' => $this->modelo,
            '{{ variable }}' => $this->variable,
            '{{ variablePlural }}' => $this->variablePlural,
            '{{ tabla }}' => $this->tabla,
            '{{ nombrePermiso }}' => $this->nombrePermiso,
            '{{ importsModelo }}' => $this->generarImportsModelo(),
            '{{ traitsModelo }}' => $this->tieneSoftDeletes ? "use SoftDeletes;\n\n    " : '',
            '{{ fillable }}' => $this->generarFillable(),
            '{{ casts }}' => $this->generarCasts(),
            '{{ relaciones }}' => $this->generarRelaciones(),
            '{{ filtros }}' => $this->generarFiltros(),
            '{{ ordenamientos }}' => $this->generarOrdenamientos(),
            '{{ reglasCrear }}' => $this->generarReglas(esEdicion: false),
            '{{ reglasEditar }}' => $this->generarReglas(esEdicion: true),
        ];

        $archivosPorStub = [
            'model.stub' => app_path("Models/{$this->modelo}.php"),
            'controller.stub' => app_path("Http/Controllers/Api/{$this->modelo}ApiController.php"),
            'request-create.stub' => app_path("Http/Requests/Api/Create{$this->modelo}ApiRequest.php"),
            'request-update.stub' => app_path("Http/Requests/Api/Update{$this->modelo}ApiRequest.php"),
            'seeder-permisos.stub' => database_path("seeders/{$this->modelo}PermisosSeeder.php"),
        ];

        foreach ($archivosPorStub as $stub => $rutaDestino) {
            $contenido = File::get(base_path("stubs/api/{$stub}"));

            File::ensureDirectoryExists(dirname($rutaDestino));
            File::put($rutaDestino, strtr($contenido, $reemplazos));

            $this->line('  <fg=green>✔</> ' . Str::after($rutaDestino, base_path() . DIRECTORY_SEPARATOR));        }
    }

    private function generarImportsModelo(): string
    {
        $imports = ['use Illuminate\Database\Eloquent\Model;'];

        if ($this->llavesForaneas) {
            $imports[] = 'use Illuminate\Database\Eloquent\Relations\BelongsTo;';
        }

        if ($this->tieneSoftDeletes) {
            $imports[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        }

        return implode("\n", $imports);
    }

    private function generarFillable(): string
    {
        return collect($this->columnas)
            ->map(fn (array $columna) => "        '{$columna['name']}',")
            ->implode("\n");
    }


    private function generarCasts(): string
    {
        $castPorTipo = [
            'integer' => 'integer',
            'decimal' => 'decimal:2',
            'boolean' => 'boolean',
            'date' => 'date:Y-m-d',
            'datetime' => 'datetime',
        ];

        return collect($this->columnas)
            ->filter(fn (array $columna) => isset($castPorTipo[$this->tipoSimple($columna)]))
            ->map(fn (array $columna) => "            '{$columna['name']}' => '{$castPorTipo[$this->tipoSimple($columna)]}',")
            ->implode("\n");
    }


    private function generarRelaciones(): string
    {
        $metodos = [];

        foreach ($this->llavesForaneas as $columna => $tablaForanea) {
            $modeloRelacionado = $this->modeloDeTabla($tablaForanea, $columna);
            $nombreMetodo = Str::camel(Str::beforeLast($columna, '_id'));

            if (! File::exists(app_path("Models/{$modeloRelacionado}.php"))) {
                $this->warn("  ⚠ El modelo {$modeloRelacionado} (tabla {$tablaForanea}) aún no existe; genéralo para que funcione {$nombreMetodo}().");
            }

            $metodos[] = "    public function {$nombreMetodo}(): BelongsTo\n"
                . "    {\n"
                . "        return \$this->belongsTo({$modeloRelacionado}::class, '{$columna}');\n"
                . '    }';
        }

        return $metodos ? "\n" . implode("\n\n", $metodos) . "\n" : '';
    }


    private function modeloDeTabla(string $tablaForanea, string $columna): string
    {
        $patronTabla = '/\$table\s*=\s*\'' . preg_quote($tablaForanea, '/') . '\'/';

        foreach (File::files(app_path('Models')) as $archivoModelo) {
            if (preg_match($patronTabla, $archivoModelo->getContents())) {
                return $archivoModelo->getFilenameWithoutExtension();
            }
        }

        return Str::studly(Str::beforeLast($columna, '_id'));
    }


    private function generarFiltros(): string
    {
        $filtros = collect($this->columnas)->map(fn (array $columna) => $this->tipoSimple($columna) === 'string'
            ? "                '{$columna['name']}',"
            : "                AllowedFilter::exact('{$columna['name']}'),");

        return "[\n" . $filtros->implode("\n") . "\n            ]";
    }

    private function generarOrdenamientos(): string
    {
        $campos = collect($this->columnas)
            ->map(fn (array $columna) => "                '{$columna['name']}',")
            ->prepend("                'id',");

        return "[\n" . $campos->implode("\n") . "\n            ]";
    }

    private function generarReglas(bool $esEdicion): string
    {
        $lineas = [];

        foreach ($this->columnas as $columna) {
            $nombreColumna = $columna['name'];
            $reglas = array_map(
                fn (string $regla) => "'{$regla}'",
                [$this->reglaObligatoriedad($columna), ...$this->reglasDeTipo($columna)]
            );

            if (isset($this->llavesForaneas[$nombreColumna])) {
                $tablaForanea = $this->llavesForaneas[$nombreColumna];
                $reglaExiste = "Rule::exists('{$tablaForanea}', 'id')";


                if (Schema::hasColumn($tablaForanea, 'deleted_at')) {
                    $reglaExiste .= "->whereNull('deleted_at')";
                }

                $reglas[] = $reglaExiste;
            }

            if (in_array($nombreColumna, $this->columnasUnicas)) {
                $reglaUnica = "Rule::unique('{$this->tabla}', '{$nombreColumna}')";

                if ($esEdicion) {
                    $reglaUnica .= "->ignore(\$this->route('{$this->variable}'))";
                }

                $reglas[] = $reglaUnica;
            }

            $lineas[] = "            '{$nombreColumna}' => [" . implode(', ', $reglas) . '],';
        }

        return implode("\n", $lineas);
    }

    private function reglaObligatoriedad(array $columna): string
    {
        if ($columna['nullable']) {
            return 'nullable';
        }

        return $columna['default'] !== null ? 'sometimes' : 'required';
    }

    private function reglasDeTipo(array $columna): array
    {
        return match ($this->tipoSimple($columna)) {
            'string' => array_values(array_filter([
                'string',
                $this->esCorreo($columna) ? 'email' : null,
                $this->longitudMaxima($columna),
            ])),
            'integer' => ['integer'],
            'decimal' => ['numeric'],
            'boolean' => ['boolean'],
            'date', 'datetime' => ['date'],
            default => [],
        };
    }

    private function esCorreo(array $columna): bool
    {
        return in_array($columna['name'], ['correo', 'email']);
    }


    private function longitudMaxima(array $columna): ?string
    {
        return preg_match('/\((\d+)\)/', $columna['type'], $coincidencias) ? "max:{$coincidencias[1]}" : null;
    }


    private function tipoSimple(array $columna): string
    {
        return match (strtolower($columna['type_name'])) {
            'nvarchar', 'varchar', 'nchar', 'char', 'text', 'ntext' => 'string',
            'bigint', 'int', 'integer', 'smallint', 'tinyint' => 'integer',
            'decimal', 'numeric', 'float', 'double', 'real', 'money' => 'decimal',
            'bit', 'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'datetime2', 'smalldatetime', 'timestamp' => 'datetime',
            default => 'string',
        };
    }

    private function agregarRuta(): void
    {
        $rutaArchivo = base_path('routes/api.php');
        $contenido = File::get($rutaArchivo);
        $controlador = "App\\Http\\Controllers\\Api\\{$this->modelo}ApiController";

        if (str_contains($contenido, "{$controlador}::class")) {
            $this->warn("La ruta de {$this->tabla} ya existe en routes/api.php.");

            return;
        }

        if (! str_contains($contenido, self::MARCA_RUTAS)) {
            $this->error('No se encontró la marca "' . self::MARCA_RUTAS . '" en routes/api.php.');

            return;
        }

        $ruta = "Route::apiResource('{$this->tabla}', {$controlador}::class)"
            . "->parameters(['{$this->tabla}' => '{$this->variable}']);";

        File::put($rutaArchivo, str_replace(self::MARCA_RUTAS, $ruta . "\n    " . self::MARCA_RUTAS, $contenido));

        $this->info(" Ruta agregada: api/{$this->tabla}");
    }

    private function agregarSeederPermisos(): void
    {
        $rutaArchivo = database_path('seeders/DatabaseSeeder.php');
        $contenido = File::get($rutaArchivo);
        $seeder = "{$this->modelo}PermisosSeeder::class,";

        if (str_contains($contenido, $seeder)) {
            return;
        }

        if (! str_contains($contenido, self::MARCA_SEEDERS)) {
            $this->error('No se encontró la marca "' . self::MARCA_SEEDERS . '" en DatabaseSeeder.php.');

            return;
        }

        File::put($rutaArchivo, str_replace(self::MARCA_SEEDERS, $seeder . "\n            " . self::MARCA_SEEDERS, $contenido));

        $this->info(" {$this->modelo}PermisosSeeder registrado en DatabaseSeeder");
    }


    private function mostrarJsonParaFront(): void
    {
        $datosFront = [
            'Modelo' => $this->modelo,
            'Ruta' => "api/{$this->tabla}",
            'Columnas' => collect($this->columnas)
                ->mapWithKeys(fn (array $columna) => [$columna['name'] => $this->tipoSimple($columna)])
                ->all(),
            'Directorio' => str_replace('_', '-', $this->tabla),
        ];

        $this->newLine();
        $this->info(' JSON para el generador del front (make:crud):');
        $this->line(json_encode($datosFront, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }


    private function camelCaseAFraseMinusculaConPlural(string $texto): string
    {
        $frase = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $texto);
        $frase = preg_replace('/(?<=[A-Z])(?=[A-Z][a-z])/', ' ', $frase);
        $palabras = explode(' ', mb_strtolower($frase));
        $ultimaPalabra = array_pop($palabras);

        $plural = match (true) {
            str_ends_with($ultimaPalabra, 'z') => substr($ultimaPalabra, 0, -1) . 'ces',
            str_ends_with($ultimaPalabra, 'ión') => preg_replace('/ión$/u', 'iones', $ultimaPalabra),
            str_ends_with($ultimaPalabra, 's') => $ultimaPalabra,
            (bool) preg_match('/[aeo]$/', $ultimaPalabra) => $ultimaPalabra . 's',
            default => $ultimaPalabra . 'es',
        };

        $palabras[] = $plural;

        return implode(' ', $palabras);
    }
}
