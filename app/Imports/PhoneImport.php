<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;   // only if first row = column names
use App\Services\SMSService;

class PhoneImport implements ToCollection, WithHeadingRow
{
    private SMSService $sms;

    public function __construct(SMSService $sms)
    {
        $this->sms = $sms;
    }

    /**
     * @param  Collection  $rows  Each item is an array representing one row.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            // If your Excel has a header called “phone”
            $phone   = trim($row['phone']);      // or $row[0] if NO header row
            $otp     = random_int(100000, 999999);
            $message = 'رمز التحقق:';           // Arabic message prefix

            try {
                $this->sms->sendSMS($phone, $otp, $message);
            } catch (\Throwable $e) {
                // log and continue; don’t abort all remaining rows
                \Log::error("SMS to {$phone} failed → ".$e->getMessage());
            }
        }
    }
}
