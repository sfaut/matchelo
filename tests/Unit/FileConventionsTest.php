<?php

test('yaml files use the .yaml extension', function () {
    $root = dirname(__DIR__, 2);
    $ignored_directories = ['.git', 'vendor', 'node_modules', 'storage', 'design_handoff_matchelo'];

    $directories = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
    $filtered = new RecursiveCallbackFilterIterator(
        $directories,
        fn (SplFileInfo $file): bool => ! ($file->isDir() && in_array($file->getFilename(), $ignored_directories, true)),
    );

    $yml_files = [];

    foreach (new RecursiveIteratorIterator($filtered) as $file) {
        if ($file->getExtension() === 'yml') {
            $yml_files[] = $file->getPathname();
        }
    }

    expect($yml_files)->toBe([]);
});
