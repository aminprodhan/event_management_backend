<?php
namespace Amin\Event\Helpers;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class Utils{
    public static function generateJWTToken($payload) {
        $key = getenv('JWT_SECRET_KEY');
        return JWT::encode($payload, $key, 'HS256');
    }
    public static function decripteJWTToken($token) {
        $key = getenv('JWT_SECRET_KEY');
        return JWT::decode($token, new Key($key, 'HS256'));
    }
    public static function getBearerToken() {
        // Try fetching headers using getallheaders() (for environments where $_SERVER is not enough)
        $headers = getallheaders();
        // Check if the Authorization header exists
        if (isset($headers['Authorization'])) {
            $authorizationHeader = $headers['Authorization'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            // For some server setups where the header is redirected
            $authorizationHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } else {
            return null;  // Return null if Authorization header not found
        }
        // Extract Bearer token from the header
        if (preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];  // Return the token
        }
        return null;  // Return null if no Bearer token is found
    }
    public static function generateSlug($string) {
        $string = strtolower($string);
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^a-z0-9-]/', '', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        return $string;
    }
    public static function setUserSession($user) {
        $_SESSION['user'] = $user;
    }
    public static function getUserSession() {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }
    public static function array2csv($data)
    {
        
        // Set headers to prompt for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="data.csv"');
        // Open output stream
        $output = fopen('php://output', 'w');
        if($data && count($data) > 0){
            fputcsv($output, array_keys((array)$data[0]));
            foreach ($data as $row) {
                fputcsv($output, (array)$row);
            }
        }
        // Close the output stream
        fclose($output);
        exit();
    }
    public static function download_send_headers() {
        $filename="attendees-" . date("Y-m-d") . ".csv";
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
    
        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
    
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }
}