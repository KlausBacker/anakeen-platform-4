<?php
namespace Anakeen\Routes\Admin;

class Plugin
{

    public function __invoke(\Slim\Http\request $request, \Slim\Http\response $response, $args)
    {
        $filePath = __DIR__."/../../AdminPlugins/dist/AdminPlugins/debug/ank-admin-plugins-components.js";
        $fileContent = file_get_contents($filePath);
        $mimeType = mime_content_type($filePath);
        $content = $response->getBody();
        $content->write($fileContent);

        return $response->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'public');
    }
}