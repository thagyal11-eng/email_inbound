<!DOCTYPE html>
<html>
<head><title>Email Sync Test</title></head>
<body style="font-family: sans-serif; padding: 20px;">
    <h1>My Localhost Inbox</h1>
    <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
        <tr>
            <th>From</th>
            <th>Subject</th>
            <th>Snippet</th>
        </tr>
        @foreach($emails as $email)
        <tr>
            <td>{{ $email->from_name }} <br> <small>{{ $email->from_email }}</small></td>
            <td>{{ $email->subject }}</td>
            <td>{{ Str::limit($email->body, 50) }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>