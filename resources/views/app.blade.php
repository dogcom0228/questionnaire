<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Questionnaire') }}</title>
    
    <!-- Load User's Styles if they have any, or provide basic resets -->
    @routes
    @questionnaireScripts
</head>
<body>
    <!-- 
        The Inertia Root. 
        Note: We look for 'questionnaire-app' first in our JS, then 'app'.
        Using a unique ID helps avoid conflicts if the user has their own Vue app on other pages.
    -->
    <div id="questionnaire-app" data-page="{{ json_encode($page) }}"></div>
</body>
</html>
