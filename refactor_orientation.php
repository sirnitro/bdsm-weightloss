<?php
$rootDir = __DIR__ . '/public'; // Change this if needed
$replacements = [
    // Lowercase
    'domme' => 'dom',
    'submissive' => 'sub',

    // Display labels (visible to users)
    'Domme' => 'Dominant',
    'Submissive' => 'Submissive', // stays, but capitalized
    'Mistress' => 'Dominant',
    'master' => 'dominant',

    // Pronouns
    'she/her' => 'they/them',
    'he/him' => 'they/them',
    'hers' => 'theirs',
    'his' => 'their',
];

$exts = ['php', 'html', 'js', 'css'];

function recursiveRefactor($dir, $replacements, $exts) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($rii as $file) {
        if ($file->isDir()) continue;

        $path = $file->getPathname();
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (!in_array($ext, $exts)) continue;

        $content = file_get_contents($path);
        $original = $content;

        foreach ($replacements as $search => $replace) {
            $content = str_ireplace($search, $replace, $content);
        }

        if ($content !== $original) {
            $backup = $path . '.bak';
            if (!file_exists($backup)) {
                copy($path, $backup);
            }
            file_put_contents($path, $content);
            echo "✅ Refactored: $path\n";
        }
    }
}

echo "🔍 Starting orientation & gender refactor in: $rootDir\n";
recursiveRefactor($rootDir, $replacements, $exts);
echo "🎉 Refactor complete. Backups created for all modified files.\n";

