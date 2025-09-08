<?php
// scripts/generate_getters_setters.php

$folder = __DIR__ . '/../src/Entity/Garage';
$files = glob($folder . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Vérifie si la classe contient déjà un getter/setter pour ne pas dupliquer
    if (strpos($content, 'function get') !== false || strpos($content, 'function set') !== false) {
        echo "Getters/setters already exist in $file, skipping...\n";
        continue;
    }

    // Trouver le nom de la classe
    if (!preg_match('/class\s+(\w+)/', $content, $matches)) {
        echo "No class found in $file, skipping...\n";
        continue;
    }
    $className = $matches[1];

    // Trouver toutes les propriétés privées
    preg_match_all('/private\s+\$([\w_]+)/', $content, $matchesProps);
    $props = $matchesProps[1];

    if (empty($props)) {
        echo "No private properties found in $file, skipping...\n";
        continue;
    }

    // Générer les getters/setters
    $methods = "\n";
    foreach ($props as $prop) {
        $ucProp = ucfirst($prop);

        $methods .= "    public function get$ucProp()\n    {\n";
        $methods .= "        return \$this->$prop;\n";
        $methods .= "    }\n\n";

        $methods .= "    public function set$ucProp(\$value)\n    {\n";
        $methods .= "        \$this->$prop = \$value;\n";
        $methods .= "        return \$this;\n";
        $methods .= "    }\n\n";
    }

    // Ajouter les méthodes avant la dernière accolade de fermeture de la classe
    $content = preg_replace('/}\s*$/', $methods . "}\n", $content);

    file_put_contents($file, $content);

    echo "Generated getters/setters for $className\n";
}