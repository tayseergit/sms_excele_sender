<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SMSService;
use Maatwebsite\Excel\Facades\Excel;

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
            'date'    => 'required|date_format:Y-m-d',
        ]);

        $msgBody   = $validated['message'];     // الرسالة الأساسيّة
        $dateBody  = "بتاريخ".$validated['date'];        // التاريخ كنص

        /* 2 ─── قراءة الملف ──────────────────────────────────────────────── */
        $rows = Excel::toCollection(null, $validated['file'])[0]; // الورقة الأولى

        $results = [];

        /* 3 ─── إرسال الرسائل ───────────────────────────────────────────── */
        foreach ($rows as $row) {
            $raw = trim((string) $row[0]); // الرقم كما هو في الخلية

            // 3-A) تحقق من تنسيق الرقم (9 أرقام فأكثر)
            if (!preg_match('/^\d{9,}$/', $raw)) {
                $results[] = [
                    'phone'  => $raw,
                    'status' => 'skipped',
                    'reason' => 'invalid_format',
                ];
                continue;
            }

            // 3-B) تحويله لصيغة دولية +963XXXXXXXXX
            $phone = '+963' . ltrim($raw, '0');

            try {
                /* أرسل الرسالة الأولى */
                $resp1 = $this->smsService->sendSMS($phone, $msgBody);   // ← غيّر الاسم إن لزم

                /* أرسل الرسالة الثانية (التاريخ) */
                $resp2 = $this->smsService->sendSMS($phone, $dateBody);  // ← غيّر الاسم إن لزم

                $results[] = [
                    'phone'     => $phone,
                    'status'    => 'sent',
                    'response1' => $resp1,
                    'response2' => $resp2,
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'phone'  => $phone,
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
            }
        }

        /* 4 ─── إعادة النتيجة كـ JSON ───────────────────────────────────── */
        return response()->json($results);
    }
}
