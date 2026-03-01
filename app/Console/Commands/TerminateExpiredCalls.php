<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CallSession;
use App\Services\AgoraService;
use App\Http\Controllers\Api\Student\PrivateCallController;

class TerminateExpiredCalls extends Command
{
    protected $signature = 'calls:terminate-expired';

    public function handle(AgoraService $agora, PrivateCallController $controller)
    {
        // Find calls that have exceeded their max_end_at
        $expiredCalls = CallSession::where('status', 'ongoing')
            ->where('max_end_at', '<=', now())
            ->get();

        foreach ($expiredCalls as $call) {
            // 1. Force kick from Agora (You'll need to implement kickUser in AgoraService)
            $agora->kickAllUsers($call->channel_name);

            // 2. Call your existing endCall logic to deduct balance
            // We pass a dummy request or refactor endCall to a Service
            $controller->terminateCallLogic($call);

            // 3. Broadcast to both apps that the call ended due to balance
            broadcast(new CallEndedBySystem($call->student_id, 'Balance exhausted'));
            broadcast(new CallEndedBySystem($call->teacher->user_id, 'Student balance exhausted'));
        }
    }
}
