<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Renders a template from a JSON file using layout array structure.
 *
 * @param string $templateName The template slug (e.g., 'index', 'product')
 * @return string HTML output of the full layout
 */
function renderTemplate(string $templateName): string
{
    $layout = loadThemeLayout($templateName);
    return renderLayoutArray($layout);
}

/**
 * For legacy support: render and include the full page via template
 *
 * @param string $templateName
 * @return void
 */
function renderPage(string $templateName): void
{
    $content_for_layout = renderTemplate($templateName);
    includeThemeLayout(get_defined_vars());
}

/**
 * Includes a section PHP file with settings + blocks
 *
 * @param string $type
 * @param array $data
 */

function renderSection(string $type, array $data = []): void
{
    includeSection($type, $data);
}

/**
 * Includes a snippet PHP file with settings.
 */
function renderSnippet(string $name, array $settings = []): void
{
    includeSnippet($name, $settings);
}

