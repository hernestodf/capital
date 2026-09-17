<?php
// error_diagnoser.php - Diagnóstico de erros PHP
// Uso: php error_diagnoser.php [--file=caminho] [--json]

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Connection;

class ErrorDiagnoser {
    private array $patterns = [
        'include' => ['pattern' => 'include\s*\(', 'severity' => 'high', 'fix' => 'Use include_once ou verifique se o arquivo existe'],
        'undefined_function' => ['pattern' => 'Call to undefined function', 'severity' => 'critical', 'fix' => 'Verifique o namespace ou include do arquivo'],
        'undefined_variable' => ['pattern' => 'Undefined variable', 'severity' => 'medium', 'fix' => 'Inicialize a variável antes de usar'],
        'sql_error' => ['pattern' => 'SQL', 'severity' => 'high', 'fix' => 'Verifique a query SQL e os dados de conexão'],
        'permission_denied' => ['pattern' => 'Permission denied', 'severity' => 'high', 'fix' => 'Verifique permissões de arquivo/pasta'],
    ];

    public function analyzeLog(string $logFile): array {
        $results = [];
        
        if (!file_exists($logFile)) {
            return [['error' => 'Arquivo não encontrado', 'file' => $logFile]];
        }

        $lines = array_slice(file($logFile), -100);
        
        foreach ($lines as $line) {
            foreach ($this->patterns as $type => $config) {
                if (stripos($line, $config['pattern']) !== false) {
                    $results[] = [
                        'type' => $type,
                        'severity' => $config['severity'],
                        'fix' => $config['fix'],
                        'line' => trim($line)
                    ];
                }
            }
        }
        
        return $results;
    }

    public function checkDatabase(): array {
        $results = [];
        
        try {
            $conn = Connection::get();
            $result = $conn->query("SELECT 1 as test");
            $results['database'] = 'ok';
        } catch (Exception $e) {
            $results['database'] = 'error: ' . $e->getMessage();
        }
        
        return $results;
    }

    public function analyzeCode(string $dir): array {
        $results = [];
        $phpFiles = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($phpFiles as $file) {
            if ($file->getExtension() !== 'php') continue;
            
            $content = file_get_contents($file->getPathname());
            
            if (stripos($content, 'mysql_query(') !== false && stripos($content, 'mysqli') === false) {
                $results[] = [
                    'file' => $file->getPathname(),
                    'issue' => 'Use mysqli preparado ao invés de mysql_query',
                    'severity' => 'medium'
                ];
            }
        }
        
        return $results;
    }
}

$options = array_merge(['name' => null, 'value' => null], $argv);
$logFile = null;
$json = false;

for ($i = 1; $i < count($options); $i++) {
    if (strpos($options[$i], '--file=') === 0) {
        $logFile = substr($options[$i], 7);
    }
    if ($options[$i] === '--json') {
        $json = true;
    }
}

$diagnoser = new ErrorDiagnoser();

$results = [
    'timestamp' => date('c'),
    'database' => $diagnoser->checkDatabase(),
    'code_issues' => $diagnoser->analyzeCode('/var/www/html/capital/src/')
];

if ($logFile) {
    $results['log_analysis'] = $diagnoser->analyzeLog($logFile);
}

if ($json) {
    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);
} else {
    echo json_encode($results, JSON_PRETTY_PRINT);
}