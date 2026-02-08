<?php
/**
 * 简易留言板 - 存在反射型 XSS 和存储型 XSS 漏洞
 * 仅供安全学习和研究使用
 */

$messages_file = __DIR__ . '/messages.json';

// 加载已有留言
function load_messages() {
    global $messages_file;
    if (file_exists($messages_file)) {
        $data = file_get_contents($messages_file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// 保存留言
function save_message($username, $content) {
    global $messages_file;
    $messages = load_messages();
    $messages[] = [
        'username' => $username,     // 漏洞：未对用户输入做任何过滤或转义
        'content'  => $content,      // 漏洞：未对用户输入做任何过滤或转义
        'time'     => date('Y-m-d H:i:s')
    ];
    file_put_contents($messages_file, json_encode($messages, JSON_UNESCAPED_UNICODE));
}

// 处理表单提交（存储型 XSS）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['content'])) {
    save_message($_POST['username'], $_POST['content']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>留言板 - XSS 漏洞演示</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Microsoft YaHei", sans-serif; background: #f0f2f5; padding: 30px; }
        .container { max-width: 750px; margin: 0 auto; }
        h1 { text-align: center; color: #333; margin-bottom: 25px; }
        .search-box, .post-box, .message-list { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .search-box h3, .post-box h3 { margin-bottom: 12px; color: #555; }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; margin-bottom: 10px; }
        textarea { height: 80px; resize: vertical; }
        button { background: #1890ff; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; cursor: pointer; font-size: 14px; }
        button:hover { background: #40a9ff; }
        .message { border-bottom: 1px solid #eee; padding: 14px 0; }
        .message:last-child { border-bottom: none; }
        .message .meta { color: #999; font-size: 12px; margin-bottom: 6px; }
        .message .user { color: #1890ff; font-weight: bold; }
        .message .body { color: #333; line-height: 1.6; }
        .search-result { background: #fffbe6; border-left: 3px solid #faad14; padding: 12px; margin-top: 10px; border-radius: 4px; }
        .empty { text-align: center; color: #999; padding: 30px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>📝 在线留言板</h1>

    <!-- 搜索区域：存在反射型 XSS -->
    <div class="search-box">
        <h3>🔍 搜索留言</h3>
        <form method="GET">
            <input type="text" name="q" placeholder="输入关键词搜索..." value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>">
            <button type="submit">搜索</button>
        </form>
        <?php if (isset($_GET['q']) && $_GET['q'] !== ''): ?>
            <div class="search-result">
                <!-- 漏洞：直接将用户输入的搜索词输出到页面，未做 HTML 转义 -->
                您搜索的是：<strong><?php echo $_GET['q']; ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <!-- 发布留言区域 -->
    <div class="post-box">
        <h3>✏️ 发布留言</h3>
        <form method="POST">
            <input type="text" name="username" placeholder="你的昵称" required>
            <textarea name="content" placeholder="写点什么吧..." required></textarea>
            <button type="submit">发布</button>
        </form>
    </div>

    <!-- 留言展示区域：存在存储型 XSS -->
    <div class="message-list">
        <h3>💬 全部留言</h3>
        <?php
        $messages = load_messages();
        if (empty($messages)):
        ?>
            <div class="empty">暂无留言，快来抢沙发吧！</div>
        <?php
        else:
            // 倒序显示，最新的在上面
            foreach (array_reverse($messages) as $msg):
        ?>
            <div class="message">
                <div class="meta">
                    <!-- 漏洞：直接输出用户名和内容，未使用 htmlspecialchars() 转义 -->
                    <span class="user"><?php echo $msg['username']; ?></span>
                    &nbsp;·&nbsp; <?php echo $msg['time']; ?>
                </div>
                <div class="body"><?php echo $msg['content']; ?></div>
            </div>
        <?php
            endforeach;
        endif;
        ?>
    </div>
</div>
</body>
</html>
