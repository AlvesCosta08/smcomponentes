<?php
class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public static function error($message, $statusCode = 400) {
        self::json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
    
    public static function success($message = '', $data = null) {
        $response = ['success' => true];
        if ($message) $response['message'] = $message;
        if ($data !== null) $response['data'] = $data;
        self::json($response);
    }
}
?>