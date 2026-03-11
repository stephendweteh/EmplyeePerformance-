<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Updates Export</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        h1 { font-size: 18px; margin: 0; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <h1>Employee Updates Export</h1>
    <p>Date: {{ $selectedDate }}</p>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Primary Team</th>
                <th>Wins</th>
                <th>Impact</th>
                <th>Blockers</th>
                <th>Rating</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($updates as $update)
                @php($review = $update->reviews->first())
                <tr>
                    <td>{{ $update->user->name }}</td>
                    <td>{{ $update->user->team?->name }}</td>
                    <td>{{ $update->wins }}</td>
                    <td>{{ $update->business_impact }}</td>
                    <td>{{ $update->blockers }}</td>
                    <td>{{ $review?->rating ? $review->rating.'/10' : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
