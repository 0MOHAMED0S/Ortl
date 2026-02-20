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
}
