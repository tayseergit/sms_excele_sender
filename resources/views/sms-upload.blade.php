<!DOCTYPE html>
<html>
<head>
    <title>Bulk SMS Sender</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; }
        input, select, button { padding: 8px; width: 100%; margin-top: 10px; }
        .results { margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h2>Send SMS from Excel</h2>

    <form id="smsForm" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xlsx,.xls" required>
        <input type="text" name="message" placeholder="Message body" required>

        <!-- تاريخ -->
        <input type="date" id="dateInput" required>

        <!-- اختيار رقم 1‑12 -->
        <select id="numberInput" name="number" required>
            <option value="" disabled selected>اختر ساعة (1‑12)</option>
            <!-- إنشاء الخيارات بـ JS في الأسفل أو اكتبها يدويًا -->
        </select>

        <button type="submit">Upload &amp; Send</button>
    </form>

    <div class="results" id="results"></div>

    <script>
        /* توليد خيارات 1‑12 تلقائيًا */
        const sel = document.getElementById('numberInput');
        for (let i = 1; i <= 12; i++) {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = i;
            sel.appendChild(opt);
        }

        /* عند الإرسال */
        document.getElementById('smsForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const data = new FormData(e.target);

            /* قصّ السنة من التاريخ وإرسال m‑d فقط */
            const dateVal = document.getElementById('dateInput').value; // YYYY‑MM‑DD
            if (dateVal) {
                const [, m, d] = dateVal.split('-');
                data.set('date', `${m}-${d}`);         // m-d
            }

            /* الرقم يُرسل تلقائيًا لأن له name="number" */

            const res = await fetch('/send-sms-excel', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });

            document.getElementById('results').textContent =
                JSON.stringify(await res.json(), null, 2);
        });
    </script>
</body>
</html>
