<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiSecurityController extends AbstractController
{
    // API密钥（实际项目中应存储在环境变量中）
    private const API_SECRET_KEY = 'your_secret_key_here';
    
    // 时间戳有效时间（秒）
    private const TIMESTAMP_VALIDITY = 300; // 5分钟

    /**
     * 生成API签名
     */
    private function generateSignature(array $params, string $secretKey): string
    {
        // 按键名排序参数
        ksort($params);
        
        // 拼接参数
        $queryString = http_build_query($params);
        
        // 生成签名：参数字符串 + 密钥，然后进行MD5加密
        return md5($queryString . $secretKey);
    }

    /**
     * 验证请求签名
     */
    private function verifySignature(Request $request): bool
    {
        // 获取请求参数
        $params = $request->request->all();
        if (empty($params)) {
            $params = json_decode($request->getContent(), true) ?? [];
        }
        
        // 获取必要参数
        $timestamp = $params['timestamp'] ?? '';
        $signature = $params['signature'] ?? '';
        $token = $params['token'] ?? '';
        
        // 验证必要参数是否存在
        if (empty($timestamp) || empty($signature) || empty($token)) {
            return false;
        }
        
        // 验证时间戳有效性
        if (abs(time() - (int)$timestamp) > self::TIMESTAMP_VALIDITY) {
            return false;
        }
        
        // 验证Token（这里简化处理，实际应查询数据库）
        if ($token !== 'valid_token_example') {
            return false;
        }
        
        // 重新生成签名进行验证
        $checkParams = $params;
        unset($checkParams['signature']); // 移除签名参数
        
        $expectedSignature = $this->generateSignature($checkParams, self::API_SECRET_KEY);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * API接口示例
     */
    #[Route('/api/secure-data', name: 'api_secure_data', methods: ['POST'])]
    public function getSecureData(Request $request): JsonResponse
    {
        // 验证签名
        if (!$this->verifySignature($request)) {
            return $this->json([
                'code' => 401,
                'message' => 'Unauthorized: Invalid signature'
            ], 401);
        }
        
        // 返回安全数据
        return $this->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'secure_info' => 'This is protected data',
                'timestamp' => time()
            ]
        ]);
    }

    /**
     * 获取临时Token接口（实际项目中应有用户认证）
     */
    #[Route('/api/get-token', name: 'api_get_token', methods: ['POST'])]
    public function getToken(Request $request): JsonResponse
    {
        // 这里应该有用户认证逻辑
        // 简化示例直接返回token
        return $this->json([
            'code' => 200,
            'message' => 'Success',
            'data' => [
                'token' => 'valid_token_example',
                'expires_in' => 3600 // 1小时过期
            ]
        ]);
    }
}