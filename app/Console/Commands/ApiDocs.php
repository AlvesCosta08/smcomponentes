<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ApiDocs extends Command
{
    protected $signature = 'api:docs {--format=html}';
    protected $description = 'Gerar documentação da API';

    public function handle()
    {
        $routes = $this->getApiRoutes();
        
        if ($this->option('format') === 'html') {
            $this->generateHtml($routes);
        } else {
            $this->generateJson($routes);
        }
    }

    private function getApiRoutes()
    {
        $routes = [];
        $allRoutes = Route::getRoutes();

        foreach ($allRoutes as $route) {
            $uri = $route->uri();
            
            if (!str_starts_with($uri, 'api/')) {
                continue;
            }

            $routes[] = [
                'method' => implode('|', $route->methods()),
                'uri' => '/'.$uri,
                'name' => $route->getName(),
                'controller' => $this->getControllerName($route),
                'middleware' => implode(', ', $route->middleware()),
            ];
        }

        return $routes;
    }

    private function getControllerName($route)
    {
        $action = $route->getActionName();
        if ($action === 'Closure') {
            return 'Closure';
        }
        return Str::afterLast($action, '\\');
    }

    private function generateJson($routes)
    {
        $data = [
            'api' => [
                'name' => 'SM Componentes API',
                'version' => '1.0.0',
                'generated_at' => now()->toDateTimeString(),
            ],
            'routes' => $routes
        ];
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        $this->ensureDirectoryExists(storage_path('api-docs'));
        file_put_contents(storage_path('api-docs/api-routes.json'), $json);
        
        $this->info('✅ JSON gerado: storage/api-docs/api-routes.json');
        $this->info('📊 Total de rotas: ' . count($routes));
    }

    private function generateHtml($routes)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>SM Componentes API - Documentação</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #3490dc; padding-bottom: 10px; }
        .route { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 4px; border-left: 4px solid #3490dc; }
        .method { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-right: 10px; }
        .GET { background: #28a745; color: white; }
        .POST { background: #007bff; color: white; }
        .PUT { background: #fd7e14; color: white; }
        .DELETE { background: #dc3545; color: white; }
        .PATCH { background: #6f42c1; color: white; }
        .uri { font-weight: bold; color: #333; }
        .meta { color: #666; font-size: 14px; margin-top: 5px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; background: #e9ecef; color: #495057; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 14px; border-top: 1px solid #dee2e6; padding-top: 20px; }
        .info { background: #d1ecf1; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SM Componentes - API</h1>
        <div class="info">
            <strong>Total de rotas:</strong> '.count($routes).' | 
            <strong>Gerado em:</strong> '.now()->format('d/m/Y H:i:s').'
        </div>
        <hr>';

        foreach ($routes as $route) {
            $methodClass = explode('|', $route['method'])[0];
            $html .= '
        <div class="route">
            <div>
                <span class="method '.$methodClass.'">'.$route['method'].'</span>
                <span class="uri">'.$route['uri'].'</span>
            </div>
            <div class="meta">
                <strong>Controller:</strong> '.$route['controller'];
            
            if ($route['name']) {
                $html .= ' | <strong>Nome:</strong> '.$route['name'];
            }
            
            if ($route['middleware']) {
                $html .= ' | <strong>Middleware:</strong> <span class="badge">'.$route['middleware'].'</span>';
            }
            
            $html .= '
            </div>
        </div>';
        }

        $html .= '
        <div class="footer">
            <p>Documentação gerada automaticamente pelo Laravel</p>
        </div>
    </div>
</body>
</html>';

        $this->ensureDirectoryExists(storage_path('api-docs'));
        file_put_contents(storage_path('api-docs/api-documentation.html'), $html);
        
        $this->info('✅ HTML gerado: storage/api-docs/api-documentation.html');
        $this->info('📊 Total de rotas: ' . count($routes));
        $this->info('🌐 Abra o arquivo no navegador');
    }

    private function ensureDirectoryExists($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}