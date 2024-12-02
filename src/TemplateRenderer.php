<?php
// src/TemplateRenderer.php

class TemplateRenderer
{
    public static function render($templatePath, $variables = [])
    {
        // Load the template content
        $templateContent = file_get_contents($templatePath);

        // Replace placeholders with actual values
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $templateContent = str_replace($placeholder, $value, $templateContent);
        }

        return $templateContent;
    }
}
