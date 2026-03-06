<?php

if (! function_exists('render_php_view')) {
    /**
     * Rend une vue PHP (fichier .php) avec les données fournies.
     * Pas de Blade : HTML + logique PHP directe.
     *
     * @param  string  $template  Nom du template (ex: 'site.home') → resources/views/site/home.php
     * @param  array<string, mixed>  $data  Données à extraire dans la vue
     * @return string HTML rendu
     */
    function render_php_view(string $template, array $data = []): string
    {
        $path = resource_path('views/' . str_replace('.', '/', $template) . '.php');

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Vue PHP introuvable: {$path}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $path;

        return (string) ob_get_clean();
    }
}
