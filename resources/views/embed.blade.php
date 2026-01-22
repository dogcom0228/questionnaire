<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $questionnaire->title }}</title>
    
    <!-- Scripts -->
    @questionnaireScripts
</head>
<body>
    <div id="questionnaire-app" 
         data-questionnaire="{{ json_encode($questionnaire) }}"
         data-options="{{ json_encode($options) }}">
    </div>
</body>
</html>
