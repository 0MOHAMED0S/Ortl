<?php

namespace App\Services;

use Peterujah\Agora\Agora;
use Peterujah\Agora\User;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class AgoraService
{
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
    // In AgoraService.php
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
