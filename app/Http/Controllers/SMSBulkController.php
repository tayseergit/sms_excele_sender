<?php

namespace App\Http\Controllers;

use App\Jobs\SendSms;
use Illuminate\Http\Request;
use App\Services\SMSService;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isEmpty;

class SMSBulkController extends Controller
{
    protected SMSService $smsService;

    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * POST /send-sms-excel
     * Body:
     *   file    => .xlsx | .xls   (رقم الهاتف في أول عمود، صفوف بلا رؤوس)
     *   message => نص الرسالة
     *   date    => YYYY-MM-DD     (من <input type="date">)
     */
    public function sendFromExcel(Request $request)
    {
        /* 1 ─── التحقّق من المدخلات ───────────────────────────────────────── */
       $validated = $request->validate([
    'file'    => 'required|file|mimes:xlsx,xls',
    'message' => 'required|string',
    'date'    => 'required|date_format:m-d',
    'number'  => 'required|integer|between:1,12',  // ← حقل جديد
]);

$msgBody  = $validated['message'];
$dateBody = ' بتاريخ ' . $validated['date'];
$numBody  = ' الساعة ' . $validated['number'];

$fullMessage = "{$msgBody}{$dateBody}{$numBody}";
        // Log::info('FULL SMS BODY: '.$fullMessage);
        // pt
        /* 2 ─── قراءة الملف ──────────────────────────────────────────────── */
        $rows = Excel::toCollection(null, $validated['file'])[0]; // الورقة الأولى

        $results = [];

        /* 3 ─── إرسال الرسائل ───────────────────────────────────────────── */
        foreach ($rows as $row) {
            $raw = trim((string)$row[0]);
            if($raw==null || $raw==''  ) continue;
            SendSms::dispatch($raw, $validated['message'], "بتاريخ".$validated['date']);
        }

        return response()->json("SMS are being sent in the background");
    }

    /**
     * @param string $raw
     * @param mixed $msgBody
     * @param string $dateBody
     * @param array $results
     * @return array
     */
    public function send(string $raw, mixed $msgBody, string $dateBody, array $results): array
    {
// 3-B) تحويله لصيغة دولية +963XXXXXXXXX
        $phone = '+963' . ltrim($raw, '0');

            try {
                /* أرسل الرسالة الأولى */
                $resp1 = $this->smsService->sendSMS($phone, $fullMessage);   // ← غيّر الاسم إن لزم

                /* أرسل الرسالة الثانية (التاريخ) */
                // $resp2 = $this->smsService->sendSMS($phone, $dateBody);  // ← غيّر الاسم إن لزم

                $results[] = [
                    'phone'     => $phone,
                    'status'    => 'sent',
                    'response1' => $resp1,
                    // 'response2' => $resp2,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'phone'  => $phone,
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
            }
        }
        return $results;
    }
}
