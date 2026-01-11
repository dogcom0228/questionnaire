<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Response Submitted</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; color: #333; }
        .container { max-width: 640px; margin: 0 auto; padding: 16px; }
        .header { margin-bottom: 12px; }
        .meta { font-size: 14px; color: #666; }
        .answers { margin-top: 16px; }
        .answer { margin-bottom: 8px; }
        .question { font-weight: 600; }
        .value { margin-left: 6px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>New Response Submitted</h2>
        <p>A new response has been submitted for "{{ $questionnaire->title }}".</p>
    </div>
    <div class="meta">
        <p><strong>Response ID:</strong> {{ $response->id }}</p>
        <p><strong>Submitted At:</strong> {{ optional($response->created_at)->toIso8601String() }}</p>
        <p><strong>IP Address:</strong> {{ $response->ip_address ?? 'N/A' }}</p>
        <p><strong>Respondent:</strong> {{ $response->respondent_type ? ($response->respondent_type.'#'.$response->respondent_id) : 'Guest' }}</p>
    </div>

    <div class="answers">
        <h3>Answers</h3>
        @php($response->loadMissing(['answers.question']))
        @foreach ($response->answers as $answer)
            <div class="answer">
                <span class="question">{{ $answer->question?->content ?? ('Question #'.$answer->question_id) }}:</span>
                <span class="value">{{ $answer->formatted_value }}</span>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
