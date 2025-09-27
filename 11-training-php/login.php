<?php
// Không dùng session mặc định nữa
// session_start();

require 'vendor/autoload.php'; // Dùng cho Predis
use Predis\Client;

require_once 'models/UserModel.php';
$userModel = new UserModel();

// Kết nối Redis container
$redis = new Client([
    'scheme' => 'tcp',
    'host' => 'redis', // host = tên service trong docker-compose
    'port' => 6379
]);

if (!empty($_POST['submit'])) {
    $users = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];
    $user = NULL;

    // Kiểm tra thông tin đăng nhập
    if ($user = $userModel->auth($users['username'], $users['password'])) {
        // Đăng nhập thành công → Tạo token
        $token = bin2hex(random_bytes(32)); // tạo token ngẫu nhiên

        // Lưu thông tin user vào Redis (dùng token làm key)
        $redis->hmset("session:$token", [
            'id' => $user[0]['id'],
            'username' => $users['username'],
            'message' => 'Login successful'
        ]);

        // Đặt thời gian sống cho session (VD: 1 giờ = 3600 giây)
        $redis->expire("session:$token", 3600);
        setcookie('token', $token);
        // Gửi token xuống localStorage qua JS
        echo "<script>
            localStorage.setItem('token', '$token');
            window.location.href = 'list_users.php';
        </script>";

    } else {
        // Đăng nhập thất bại
        $redis->set("login_message", "Login failed");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php' ?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px">
                    <a href="#">Forgot password?</a>
                </div>
            </div>

            <div style="padding-top:30px" class="panel-body">
                <form method="post" class="form-horizontal" role="form">
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username" placeholder="username or email">
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                            <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account!
                            <a href="form_user.php">
                                Sign Up Here
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
