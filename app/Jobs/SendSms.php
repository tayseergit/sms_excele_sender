<?php

namespace App\Jobs;

use App\Services\SMSService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $raw,
        protected mixed $msgBody,
        protected string $dateBody
    ) {}

    public function handle(SMSService $smsService): void
    {
        $phone = '+963' . ltrim($this->raw, '0');
        Log::info($phone);

        try {
            $resp1 = $smsService->sendSMS($phone, $this->msgBody);
            $resp2 = $smsService->sendSMS($phone, $this->dateBody);
        } catch (\Throwable $e) {
            Log::error("Failed to send SMS to {$phone}: " . $e->getMessage());
        }
    }
}
