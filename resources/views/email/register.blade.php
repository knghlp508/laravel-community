<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>确认您的邮箱</title>
</head>
<body>
    <h1>您好！请确认您的邮箱</h1>
    <a href="{{ url('verify/'.$confirm_code) }}">点击确认</a>
</body>
</html>