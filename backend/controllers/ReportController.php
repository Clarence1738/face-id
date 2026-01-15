<?php

require_once __DIR__ . '/../models/RecognitionLog.php';

class ReportController {
    private $logModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->logModel = new RecognitionLog($conn);
    }

    public function getReport() {
        try {
            $logs = $this->logModel->getSuccessfulCheckIns();
            $this->sendResponse(200, $logs);
        } catch (Exception $e) {
            $this->sendResponse(500, ['message' => 'Failed to retrieve report: ' . $e->getMessage()]);
        }
    }

    private function sendResponse($code, $data) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
?>
