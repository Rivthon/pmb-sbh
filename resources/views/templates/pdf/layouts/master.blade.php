<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="PMB">
    <meta itemprop="description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="https://pmb.sbh.ac.id">
    <meta property="og:type" content="website">
    <meta property="og:title" content="PMB SBH">
    <meta property="og:description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PMB SBH">
    <meta name="twitter:description" content="PMB SBH - Penerimaan Mahasiswa Baru">

    <title>{{ config('app.name') }} - @yield('title')</title>
</head>
<style>
    body {
        position: relative;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        -premailer-cellpadding: 0;
        -premailer-cellspacing: 0;
        -premailer-width: 570px;
        background-color: #ffffff;
        margin: 0 auto;
        padding: 0;
        font-size: 14px;
    }

    .bg-primary {
        background-color: #ffa639;
    }

    .vertical-middle {
        vertical-align: middle;
    }

    .clr-white {
        color: #fff;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .w-100 {
        width: 100%;
    }

    .bt-1 {
        border-top: 1px solid #000;
    }

    .py-2 {
        padding-top: 6px;
        padding-bottom: 6px;
    }

    .page-break {
        page-break-after: always;
    }

    .block {
        display: block
    }

    .inline-block {
        display: inline-block;
    }
</style>

@stack('style')

<body>
    @yield('content')
</body>
</html>
