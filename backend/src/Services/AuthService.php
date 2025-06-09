<?php
declare(strict_types=1);

namespace Src\Services;
use Exception;
use Src\Database;
use PDO;

class AuthSerivce {
    private PDO $db;

    public function __construct(Database $db) {
        $this->db = $db->getConnection();
    }//

    public function register(string $username, string $password): array {
        if(empty($username) || empty($password)) {
            throw new Exception('Username and password are required', 400);
        }
        $startime = microtime(true);
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
        $stmt->execute(['username' => $username, 'password' => $hashedPassword]);

        //log activity
        $execution_time = microtime(true) - $startime;
        $this->logActivity(null, 'REGISTER', "User $username registered", $execution_time);
        return ['message' => 'User registered successfully'];
    }//

    public function login(string $username, string $password): array {
        if(empty($username) || empty($password)) {
            throw new Exception('Username and password are required', 400);
        }
        $starttime = microtime(true);
        $stmt = $this->db->prepare('SELECT userid,username,password FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user && password_verify($password, $user['password'])) {
            $token = $this->generateJWT($user['id']);
            //log
            $execution_time = microtime(true) - $starttime;
            $this->logActivity($user['id'], 'LOGIN', "User $username logged in", $execution_time);

            return ['token' => $token];
        }
        throw new Exception('Invalid credentials', 401);
    }//

    public function verifyToken(string $token): bool {
        try {
            $payload = $this->decodeJWT($token);
            return $payload['exp'] > time();
         } catch (Exception) {
            return false;
        }
    }//

    public function getUserId(string $token): int {
        $payload = $this->decodeJWT($token);
        return $payload['userid'];
    }//

    private function generateJWT(int $userid): string {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'userid' => $userid,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]));
        $signature = base64_encode(hash_mac('sha256', "$header.$payload", JWT_SECRET, true));
        return "$header.$payload.$signature";
     }//

     private function decodeJWT(string $token): array {
        $parts = explode('.', $token);
         if (count($parts)!== 3) {
             throw new Exception('Invalid token format', 401);
         }
         $signature = base64_encode(hash_mac('sha256', "$parts[0].$parts[1]", JWT_SECRET, true));
         if($signature !== $parts[2]) {
             throw new Exception('Invalid token signature', 401);
         }
         return json_decode(base64_decode($parts[1]), true);
      }//end decodeJWT

    private function logActivity(?int $userid, string $action, string $description, float $execution_time): void 
    {
        $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, decription, execution_time_ms, created_at)
                                            VALUES (:userid, :action, :description, :execution_time_ms, NOW())");
        $stmt->execute([
            'user_id' => $userid,
            'action' => $action,
            'description' => $description,
            "execution_time_ms" => round($execution_time*1000,2)
        ]);
    }



}// end auth service
?>