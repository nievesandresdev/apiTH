// resources/views/social-meta.blade.php

<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:site_name" content="{{ $site_name }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
</body>
</html>