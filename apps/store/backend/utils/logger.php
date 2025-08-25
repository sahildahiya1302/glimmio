<?php
declare(strict_types=1);

class Logger {
    private string $logFile;

    public function __construct(string $logFile = null) {
        if ($logFile === null) {
            $logFile = __DIR__ . '/../../logs/app.log';
        }
        $this->logFile = $logFile;
    }

    public function log(string $level, string $message, array $context = []): void {
        $date = date('Y-m-d H:i:s');
        $contextStr = json_encode($context);
        $logEntry = "[$date] [$level] $message $contextStr" . PHP_EOL;

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function info(string $message, array $context = []): void {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void {
        $this->log('ERROR', $message, $context);
    }
}
?>
