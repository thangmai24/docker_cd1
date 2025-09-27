<?php
require 'vendor/autoload.php'; // Predis
use Predis\Client;

require_once 'models/UserModel.php';
$userModel = new UserModel();

// ============================================
// 1. Kết nối Redis
// ============================================
$redis = new Client([
    'scheme' => 'tcp',
    'host'   => 'redis', // tên service Redis trong docker-compose
    'port'   => 6379
]);

// ============================================
// 2. Kiểm tra token xác thực
// ============================================
// Lấy token từ query string hoặc header
$token = $_COOKIE['token'] ?? null;


if (!$token) {
    die("<h2>Unauthorized: Vui lòng đăng nhập trước</h2>");
}

$sessionKey = "session:$token";

$userSession = $redis->hgetall($sessionKey);

if (empty($userSession)) {
    die("<h2>Session hết hạn hoặc token không hợp lệ</h2>");
}

// ============================================
// 3. Lọc và bảo vệ input từ người dùng
// ============================================
$params = [];
if (!empty($_GET['keyword'])) {
    // Chỉ cho phép chữ, số và dấu cách
    $keyword = preg_replace("/[^a-zA-Z0-9\s]/", "", $_GET['keyword']);
    $params['keyword'] = $keyword;
}

// ============================================
// 4. Lấy danh sách người dùng từ DB
// ============================================
$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>List Users</title>
    <?php include 'views/meta.php'; ?>
</head>
<body>
   
    <?php include 'views/header.php'; ?>

    <div class="container">
        <div class="alert alert-info">
            Xin chào, <strong><?php echo htmlspecialchars($userSession['username']); ?></strong>!
            <br>
            <small>Token hiện tại: <?php echo htmlspecialchars($token); ?></small>
        </div>

        <form method="get" action="list_users.php" class="mb-3">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm username hoặc fullname">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning">
                <strong>Danh sách người dùng</strong>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <th scope="row"><?php echo htmlspecialchars($user['id']); ?></th>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($user['type']); ?></td>
                            <td>
                                <a href="form_user.php?id=<?php echo urlencode($user['id']); ?>&token=<?php echo urlencode($token); ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?php echo urlencode($user['id']); ?>&token=<?php echo urlencode($token); ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>
                                <a href="delete_user.php?id=<?php echo urlencode($user['id']); ?>&token=<?php echo urlencode($token); ?>"
                                   onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                    <i class="fa fa-eraser" aria-hidden="true" title="Delete"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark">
                Không tìm thấy người dùng nào.
            </div>
        <?php } ?>
    </div>
     <script>
        console.log("Token hiện tại: <?php echo htmlspecialchars($token); ?>");
    </script>
</body>
</html>
