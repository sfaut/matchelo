<?php

/**
 * Migrations de l'application, indexées par nom de fichier ; cache et jobs, purement techniques,
 * gardent la définition du framework.
 *
 * @return array<string, array{string}>
 */
function applicationMigrations(): array
{
    $files = glob(dirname(__DIR__, 2).'/database/migrations/*.php') ?: [];

    return collect($files)
        ->reject(fn (string $file): bool => str_contains($file, 'create_cache_table') || str_contains($file, 'create_jobs_table'))
        ->mapWithKeys(fn (string $file): array => [basename($file) => [$file]])
        ->all();
}

/**
 * Instructions $table->…(…); d'une migration, sans commentaires.
 *
 * @return list<string>
 */
function blueprintStatements(string $file): array
{
    $source = preg_replace(['#//.*$#m', '#/\*.*?\*/#s'], '', file_get_contents($file));
    preg_match_all('/\$table->[^;]+;/s', $source, $matches);

    return $matches[0];
}

test('migrations declare created_at and updated_at explicitly', function (string $file) {
    foreach (blueprintStatements($file) as $statement) {
        expect($statement)->not->toContain('->timestamps(');
    }
})->with(applicationMigrations());

test('a single column key or index is chained on the column, only multi-column ones come last', function (string $file) {
    expect(blueprintStatements($file))->not->toBeEmpty();

    foreach (blueprintStatements($file) as $statement) {
        if (preg_match('/^\$table->(primary|unique|index|foreign)\((.*)\)/s', $statement, $matches) === 1) {
            expect($matches[1])->not->toBe('foreign')
                ->and($matches[2])->toMatch('/^\[[^\]]*,[^\]]*\]/');
        }
    }
})->with(applicationMigrations());

test('temporal columns end with _at', function (string $file) {
    foreach (blueprintStatements($file) as $statement) {
        if (preg_match('/^\$table->(date|dateTime|timestamp|time)\(\'(\w+)\'/', $statement, $matches) === 1) {
            expect($matches[2])->toEndWith('_at');
        }
    }
})->with(applicationMigrations());
