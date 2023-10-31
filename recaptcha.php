<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>recaptcha-v3 for PHP</title>
</head>

<body>
    <script src="https://www.google.com/recaptcha/api.js?render=<site-public-key>"></script>

    <form action="<form-url>" method="post">
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
    </form>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('<site-public-key>', {
                action: 'submit'
            }).then(function(token) {
                document.getElementById("g-recaptcha-response").value = token;
            });
        });
    </script>
</body>

</html>

<!-- yourForm.php -->
<?php

/**
 *  Google機器人驗證 
 *  @param string $token
 *  @return bool 
 */
function recaptchaCheck($token)
{
    // $token = $_POST['g-recaptcha-response']
	if (!$token) {
		return false;
		return "機器人驗證-未驗證";
	}

	$secret_key = '<site-private-key>';
	$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $token);
	$response_data = json_decode($response, true);

	//return $token;

	if ($response_data["success"]) {
		//如果驗證成功，就進一步計算使用者分數，官方回饋分數為 0-1，分數愈接近 1 就是正常
        //低於 0.5 以下就有可能是機器人了
		if ($response_data["score"] >= 0.5) {
			return true;
		}
		return false;
	}

	//return false;
	$error_codes = $response_data["error-codes"];
	$error_messages = [];

	$error_codes = $response_data["error-codes"];
	$error_messages = [
		"missing-input-secret" => "reCAPTCHA 金鑰未提供。",
		"invalid-input-secret" => "reCAPTCHA 金鑰無效。",
		"missing-input-response" => "reCAPTCHA 回應未提供。",
		"invalid-input-response" => "reCAPTCHA 回應無效。",
		"invalid-keys" => "error key",
		"timeout-or-duplicate" => "驗證過久或重複",
		'browser-error'=>'網站需加入白名單'
	];

	$error_messages = array_map(function ($error_code) use ($error_messages) {
		return $error_messages[$error_code] ?? $error_code;
	}, $error_codes);

	// 返回包含所有錯誤原因的消息
	return "機器人驗證-失敗：" . implode(", ", $error_messages);
}

?>