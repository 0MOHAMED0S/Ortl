<?php

namespace App\Services;

use Peterujah\Agora\Agora;
use Peterujah\Agora\User;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgoraService
{
protected $appId;
    protected $customerId;
    protected $customerSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->appId = config('services.agora.app_id');
        $this->customerId = config('services.agora.customer_id') ?? env('AGORA_CUSTOMER_ID');
        $this->customerSecret = config('services.agora.customer_certificate') ?? env('AGORA_CUSTOMER_SECRET');

        $this->baseUrl = "https://api.agora.io/v1/apps/{$this->appId}/cloud_recording";
    }

    public function generateToken(string $channelName, $userId, string $role)
    {
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

        $expirationInSeconds = 3600;
        $privilegeExpireTs = now()->timestamp + $expirationInSeconds;

        $client = new Agora($appId, $appCertificate);
        $client->setExpiration($privilegeExpireTs);

        $user = (new User($userId))
            ->setChannel($channelName)
            ->setRole(
                $role === 'host' ? Roles::RTC_PUBLISHER : Roles::RTC_SUBSCRIBER
            )
            ->setPrivilegeExpire($privilegeExpireTs);

        return RtcToken::buildTokenWithUid($client, $user);
    }

    public function acquire($channelName, $recorderUid)
    {
        $response = Http::withBasicAuth($this->customerId, $this->customerSecret)
            ->post("{$this->baseUrl}/acquire", [
                'cname' => $channelName,
                'uid' => (string) $recorderUid,
                'clientRequest' => [
                    'resourceExpiredHour' => 24,
                ]
            ]);

        if ($response->successful()) {
            return $response->json('resourceId');
        }

        Log::error('Agora Acquire Error: ' . $response->body());
        return null;
    }

    public function start($resourceId, $channelName, $token, $recorderUid)
    {
        // تجهيز إعدادات مساحة التخزين الأساسية
        $storageConfig = [
            'vendor' => (int) env('AGORA_STORAGE_VENDOR', 11), // 11 تعني Cloudflare R2
            'region' => (int) env('AGORA_STORAGE_REGION', 0),
            'bucket' => env('AGORA_STORAGE_BUCKET'),
            'accessKey' => env('AGORA_STORAGE_ACCESS_KEY'),
            'secretKey' => env('AGORA_STORAGE_SECRET_KEY'),
            'fileNamePrefix' => ['records', 'sessions']
        ];

        // 🌟 السر هنا: حقن رابط Cloudflare المخصص لكي تتعرف عليه أجورا
        if (env('AGORA_STORAGE_ENDPOINT')) {
            $storageConfig['extensionParams'] = [
                'endpoint' => env('AGORA_STORAGE_ENDPOINT')
            ];
        }

        $response = Http::withBasicAuth($this->customerId, $this->customerSecret)
            ->post("{$this->baseUrl}/resourceid/{$resourceId}/mode/mix/start", [
                'cname' => $channelName,
                'uid' => (string) $recorderUid,
                'clientRequest' => [
                    'token' => $token,
                    'recordingConfig' => [
                        'maxIdleTime' => 30,
                        'streamTypes' => 2,
                        'channelType' => 0,
                        'transcodingConfig' => [
                            'height' => 480,
                            'width' => 640,
                            'bitrate' => 500,
                            'fps' => 15,
                            'mixedVideoLayout' => 1,
                            'backgroundColor' => "#000000"
                        ]
                    ],
                    'storageConfig' => $storageConfig
                ]
            ]);

        if ($response->successful()) {
            return $response->json('sid');
        }

        Log::error('Agora Start Recording Error: ' . $response->body());
        return null;
    }

public function stop($resourceId, $sid, $channelName, $recorderUid)
    {
        $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->customerId, $this->customerSecret)
            ->post("{$this->baseUrl}/resourceid/{$resourceId}/sid/{$sid}/mode/mix/stop", [
                'cname' => $channelName,
                'uid'   => (string) $recorderUid,
                // 🌟 الحل السحري هنا: إجبار لارافيل على إرسال كائن (Object) فارغ بدلاً من مصفوفة (Array)
                'clientRequest' => new \stdClass()
            ]);

        if ($response->successful()) {
            $data = $response->json();
            // الـ API يرجع لنا اسم الملف الذي تم رفعه لمساحة التخزين الخاصة بك
            $fileName = $data['serverResponse']['fileList'] ?? 'unknown.mp4';
            return $fileName;
        }

        \Illuminate\Support\Facades\Log::error('Agora Stop Recording Error: ' . $response->body());
        return null;
    }

    /**
     * ==========================================
     * 🚪 طرد جميع المستخدمين (الكود الأصلي الخاص بك)
     * ==========================================
     */
    public function kickAllUsers($channelName)
    {
        // Agora uses a REST API for "Cloud Recording" or "Rule Management"
        // To simply stop a call, you can use the "Kick out" RESTful API
        // Documentation: https://docs.agora.io/en/video-calling/develop/lock-room

        $appId = config('services.agora.app_id');
        $customerKey = config('services.agora.customer_id');
        $customerSecret = config('services.agora.customer_certificate');

        // Implementation of Agora REST API to terminate the channel
    }
}
