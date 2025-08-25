<?php
declare(strict_types=1);

class LiquidRenderer {
    private $themePath;
    
    public function __construct(string $themePath) {
        $this->themePath = $themePath;
    }
    
    public function render(string $templatePath, array $context = []): string {
        $templateContent = file_get_contents($templatePath);
        
        // Basic Liquid tag processing
        $processed = $this->processLiquidTags($templateContent, $context);
        $processed = $this->processLiquidVariables($processed, $context);
        $processed = $this->processLiquidFilters($processed, $context);
        
        return $processed;
    }
    
    private function processLiquidTags(string $content, array $context): string {
        // Process {% for %} loops
        $content = preg_replace_callback(
            '/{%\s*for\s+(\w+)\s+in\s+(\w+)\s*%}(.*?){%\s*endfor\s*%}/s',
            function($matches) use ($context) {
                $variable = $matches[1];
                $arrayName = $matches[2];
                $template = $matches[3];
                
                if (!isset($context[$arrayName]) || !is_array($context[$arrayName])) {
                    return '';
                }
                
                $result = '';
                foreach ($context[$arrayName] as $item) {
                    $itemContext = array_merge($context, [$variable => $item]);
                    $processed = $this->processLiquidVariables($template, $itemContext);
                    $result .= $processed;
                }
                
                return $result;
            },
            $content
        );
        
        // Process {% if %} conditions
        $content = preg_replace_callback(
            '/{%\s*if\s+(.+?)\s*%}(.*?)(?:{%\s*else\s*%}(.*?))?{%\s*endif\s*%}/s',
            function($matches) use ($context) {
                $condition = $matches[1];
                $ifContent = $matches[2];
                $elseContent = $matches[3] ?? '';
                
                // Simple condition evaluation
                $condition = trim($condition);
                if (isset($context[$condition]) && $context[$condition]) {
                    return $this->processLiquidVariables($ifContent, $context);
                } elseif (strpos($condition, '==') !== false) {
                    $parts = explode('==', $condition);
                    $left = trim($parts[0]);
                    $right = trim($parts[1], " '\"");
                    if (isset($context[$left]) && $context[$left] == $right) {
                        return $this->processLiquidVariables($ifContent, $context);
                    }
                }
                
                return $this->processLiquidVariables($elseContent, $context);
            },
            $content
        );

        // Convert style/javascript blocks and strip schema
        $content = preg_replace('/{%\s*style\s*%}(.*?){%\s*endstyle\s*%}/s', '<style>$1</style>', $content);
        $content = preg_replace('/{%\s*javascript\s*%}(.*?){%\s*endjavascript\s*%}/s', '<script>$1</script>', $content);
        $content = preg_replace('/{%\s*schema\s*%}(.*?){%\s*endschema\s*%}/s', '', $content);

        return $content;
    }
    
    private function processLiquidVariables(string $content, array $context): string {
        return preg_replace_callback(
            '/{{\s*(\w+(?:\.\w+)*)\s*}}/',
            function($matches) use ($context) {
                $variable = $matches[1];
                $value = $this->getNestedValue($context, $variable);
                return $value !== null ? htmlspecialchars((string)$value) : '';
            },
            $content
        );
    }
    
    private function processLiquidFilters(string $content, array $context): string {
        // Basic filter support
        $content = preg_replace_callback(
            '/{{\s*(\w+)\s*\|\s*(\w+)\s*}}/',
            function($matches) use ($context) {
                $variable = $matches[1];
                $filter = $matches[2];
                $value = $this->getNestedValue($context, $variable);
                
                switch ($filter) {
                    case 'upcase':
                        return strtoupper((string)$value);
                    case 'downcase':
                        return strtolower((string)$value);
                    case 'money':
                        return '$' . number_format((float)$value, 2);
                    case 'date':
                        return date('M d, Y', strtotime((string)$value));
                    default:
                        return htmlspecialchars((string)$value);
                }
            },
            $content
        );
        
        return $content;
    }
    
    private function getNestedValue(array $context, string $path) {
        $keys = explode('.', $path);
        $value = $context;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
}