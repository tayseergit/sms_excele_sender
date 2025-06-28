<!DOCTYPE html>
<html>
<head>
    <title>Bulk SMS Sender</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; }
        input, button { padding: 8px; width: 100%; margin-top: 10px; }
        .results { margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h2>Send SMS from Excel</h2>

    <form id="smsForm" enctype="multipart/form-data">
        <input type="file" name="file" accept=".xlsx,.xls" required>
        <input type="text" name="message" placeholder="Message body" required>
        <input type="date" id="dateInput" required>
        <button type="submit">Upload & Send</button>
    </form>

    <div class="results" id="results"></div>

    <script>
        document.getElementById('smsForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const data = new FormData(form);

            // Get and format the date
            const dateInput = document.getElementById('dateInput').value;
            if (dateInput) {
                const formattedDate = new Date(dateInput).toISOString().split('T')[0]; // "YYYY-MM-DD"
                data.append('date', formattedDate);
            }

            const res = await fetch('{{ url('/send-sms-excel') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: data
            });

            const json = await res.json();
            document.getElementById('results').textContent = JSON.stringify(json, null, 2);
        });
    </script>
</body>
</html>
