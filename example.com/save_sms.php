<?php
// 载入 .env 配置
$env = parse_ini_file(__DIR__ . '/.env');

// 数据库连接
$mysqli = new mysqli(
    $env['DB_HOST'],
    $env['DB_USERNAME'],
    $env['DB_PASSWORD'],
    $env['DB_DATABASE'],
    intval($env['DB_PORT'])
);

// 连接失败处理
if ($mysqli->connect_error) {
    http_response_code(500);
    die("❌ DB Connection failed: " . $mysqli->connect_error);
}

// 设定编码以防中文乱码
$mysqli->set_charset("utf8mb4");

// 获取 POST 数据
$simid   = $_POST['simid']   ?? '';
$type    = $_POST['type']    ?? '';
$number  = $_POST['number']  ?? '';
$time    = $_POST['time']    ?? '';
$content = $_POST['content'] ?? '';

// 验证必填字段
if (!$simid || !$type || !$number || !$time) {
    http_response_code(400);
    die("❌ Missing required fields.");
}

// 插入数据库
$stmt = $mysqli->prepare("INSERT INTO sms (simid, type, number, time, content) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $simid, $type, $number, $time, $content);

if ($stmt->execute()) {
    echo "✅ SMS saved successfully.";
} else {
    http_response_code(500);
    echo "❌ DB insert error: " . $stmt->error;
}

// 关闭连接
$stmt->close();
$mysqli->close();
?>
