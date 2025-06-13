


<?php

// 必须放在最顶部，防止任何输出前
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

$mysqli = new mysqli("localhost", "sql_maoyun_ip2_o", "5f82518affeca", "sql_maoyun_ip2_o");
if ($mysqli->connect_error) {
    die("数据库连接失败: " . $mysqli->connect_error);
}

$number = $_GET['number'] ?? '+8613712345678';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $mysqli->prepare("INSERT INTO sms (simid, type, number, time, content) VALUES (?, 'sent', ?, NOW(), ?)");
        $simid = 'xxxxxxxxxxxxx';
        $stmt->bind_param("sss", $simid, $number, $content);
        $stmt->execute();
        $stmt->close();
    }

    // 避免 form resubmission 警告 & 死循环
    header("Location: sms_box.php?number=" . urlencode($number));
    exit();
}


// 获取短信记录
$stmt = $mysqli->prepare("SELECT * FROM sms WHERE number = ? ORDER BY time ASC");
$stmt->bind_param("s", $number);
$stmt->execute();
$result = $stmt->get_result();
$sms_list = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="zh">
    
<head>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

    <meta charset="UTF-8">
    <title>短信对话 - <?= htmlspecialchars($number) ?></title>
    <style>
        body {
            font-family: sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 0;
        }
        .chat-box {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .message {
            margin: 10px 0;
            padding: 12px 16px;
            border-radius: 18px;
            display: inline-block;
            max-width: 70%;
            clear: both;
        }
        .inbox {
            background: #e5e5ea;
            float: left;
        }
        .sent {
            background: #007aff;
            color: white;
            float: right;
        }
        .timestamp {
            font-size: 12px;
            color: #999;
            clear: both;
            margin-top: 2px;
            text-align: center;
        }
        form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background: #007aff;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .scrollable {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="chat-box">
        <h2>与 <?= htmlspecialchars($number) ?> 的对话</h2>
        <div class="scrollable">
            <?php foreach ($sms_list as $sms): ?>
                <div class="message <?= $sms['type'] === 'sent' ? 'sent' : 'inbox' ?>">
                    <?= nl2br(htmlspecialchars($sms['content'])) ?>
                </div>
                <div class="timestamp"><?= $sms['time'] ?></div>
            <?php endforeach; ?>
        </div>

        <form method="post" action="sms_box.php?number=<?= urlencode($number) ?>&t=<?= time() ?>">


            <input type="text" name="content" placeholder="输入短信内容..." required>
            <button type="submit">发送</button>
        </form>
    </div>
</body>
</html>
