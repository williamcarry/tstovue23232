<?php

namespace App\Service;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use Qiniu\Config;

class QiniuUploadService
{
    private string $accessKey;
    private string $secretKey;
    private string $bucket;
    private string $domain;  // 七牛云绑定的域名
    private Auth $auth;
    private UploadManager $uploadManager;
    private BucketManager $bucketManager;

    public function __construct()
    {
        $this->accessKey = $_ENV['QINIU_AK'] ?? '';
        $this->secretKey = $_ENV['QINIU_SK'] ?? '';
        $this->bucket = $_ENV['QINIU_BUCKET'] ?? '';
        $this->domain = $_ENV['QINIU_DOMAIN'] ?? '';
        
        if (empty($this->accessKey) || empty($this->secretKey) || empty($this->bucket)) {
            throw new \Exception('七牛云配置不完整，请检查.env文件中的QINIU_AK、QINIU_SK和QINIU_BUCKET配置');
        }
        
        // 如果没有配置域名，使用默认域名
        if (empty($this->domain)) {
            $this->domain = "http://{$this->bucket}.qiniudn.com";
        }
        
        $this->auth = new Auth($this->accessKey, $this->secretKey);
        $this->uploadManager = new UploadManager();
        $this->bucketManager = new BucketManager($this->auth);
    }

    /**
     * 上传文件到七牛云
     *
     * @param string $filePath 本地文件路径
     * @param string|null $key 文件在七牛云存储的key（文件名），为空则自动生成
     * @return array 上传结果 ['success' => bool, 'url' => string, 'key' => string, 'error' => string]
     */
    public function uploadFile(string $filePath, ?string $key = null): array
    {
        try {
            // 生成上传Token
            $token = $this->auth->uploadToken($this->bucket);
            
            // 上传文件
            list($ret, $err) = $this->uploadManager->putFile($token, $key, $filePath);
            
            if ($err !== null) {
                return [
                    'success' => false,
                    'url' => '',
                    'key' => '',
                    'error' => '七牛云上传失败: ' . $err->message()
                ];
            } else {
                $url = $this->getPrivateUrl($ret['key']);
                return [
                    'success' => true,
                    'url' => $url,
                    'key' => $ret['key'],
                    'error' => ''
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'url' => '',
                'key' => '',
                'error' => '上传过程中发生异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 上传文件内容到七牛云
     *
     * @param string $content 文件内容
     * @param string|null $key 文件在七牛云存储的key（文件名），为空则自动生成
     * @return array 上传结果 ['success' => bool, 'url' => string, 'key' => string, 'error' => string]
     */
    public function uploadContent(string $content, ?string $key = null): array
    {
        try {
            // 生成上传Token
            $token = $this->auth->uploadToken($this->bucket);
            
            // 上传文件内容
            list($ret, $err) = $this->uploadManager->put($token, $key, $content);
            
            if ($err !== null) {
                return [
                    'success' => false,
                    'url' => '',
                    'key' => '',
                    'error' => '七牛云上传失败: ' . $err->message()
                ];
            } else {
                $url = $this->getPrivateUrl($ret['key']);
                return [
                    'success' => true,
                    'url' => $url,
                    'key' => $ret['key'],
                    'error' => ''
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'url' => '',
                'key' => '',
                'error' => '上传过程中发生异常: ' . $e->getMessage()
            ];
        }
    }

    /**
     * 删除七牛云上的文件
     *
     * @param string $key 文件key
     * @return bool 删除是否成功
     */
    public function deleteFile(string $key): bool
    {
        try {
            $err = $this->bucketManager->delete($this->bucket, $key);
            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取文件访问URL（私有链接，带签名，有效期1小时）
     *
     * @param string $key 文件key
     * @return string 文件访问URL
     */
    public function getPrivateUrl(string $key): string
    {
        // 生成带签名的私有URL，有效期为1小时（3600秒）
        $baseUrl = rtrim($this->domain, '/') . '/' . ltrim($key, '/');
        $signedUrl = $this->auth->privateDownloadUrl($baseUrl, 3600);
        return $signedUrl;
    }

    /**
     * 获取公共文件访问URL（不带签名）
     *
     * @param string $key 文件key
     * @return string 文件访问URL
     */
    public function getPublicUrl(string $key): string
    {
        $baseUrl = rtrim($this->domain, '/') . '/' . ltrim($key, '/');
        return $baseUrl;
    }

    /**
     * 检查七牛云配置是否有效
     *
     * @return bool 配置是否有效
     */
    public function isConfigValid(): bool
    {
        return !empty($this->accessKey) && !empty($this->secretKey) && !empty($this->bucket);
    }
}