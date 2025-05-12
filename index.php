<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPWAYF解析链接</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh; /* 使用 min-height 以确保即使内容少也能填满整个屏幕 */
            background-color: #f4f4f4;
        }

        .container {
            width: 84%;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow-y: auto; /* 添加垂直滚动条 */
            max-height: calc(100vh - 40px); /* 高度不超过可视区域减去顶部和底部间距 */
        }

        h1 {
            text-align: center;
        }

        form {
            text-align: center;
            margin-bottom: 20px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            height: 70px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
        footer {
        background-color: #f2f2f2;
        padding: 20px;
        text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
        }

        .github a {
            text-decoration: none;
            color: #333;
        }

        .github a:hover {
            color: #007bff;
        }

    </style>
</head>
<body>

<div class="container">
    <h1>害魄罗柯西，味儿啊呦浮弄</h1>
    <div class="form-container">
        <form method="post">
            <!--<label for="links">多个链接（每行一个）:</label><br>-->
            <textarea id="links" name="links" rows="10" cols="50"></textarea><br>
            <input type="submit" value="解析">
            <?php
             // 添加复制重命名配置按钮
        echo "<button onclick='copyToClipboard()' style='margin-left: 10px; background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s;'>复制Remark配置</button>";
        ?>
        </form>
    </div>

<?php
function parseVMessLink($vmess_link) {
    // 去除开头的 "vmess://" 部分
    $decoded_link = base64_decode(str_replace('vmess://', '', $vmess_link));
    // 解析 JSON 格式的数据
    $vmess_config = json_decode($decoded_link, true);
    return $vmess_config;
}

function parseLink($link) {
    $parsed_data = array();

    if (preg_match('/^(vless|vmess|socks|trojan|ss):\/\/([^:@]+):?([^@]*)@?([^\/#?]+):?([^\/]*)\/?([^#]*)#?(.*)$/', $link, $matches)) {
        $parsed_data['protocol'] = $matches[1];
        $parsed_data['password'] = $matches[2];
        $host_and_port = explode(':', $matches[4]); // 分割 Host 和 Port
        $parsed_data['host'] = $host_and_port[0]; // Host
        $parsed_data['port'] = isset($host_and_port[1]) ? $host_and_port[1] : ''; // Port
        $parsed_data['path'] = $matches[6];
        $parsed_data['query'] = $matches[7];
    }
    return $parsed_data;
}


?>
<?php




function getIpInfo($host) {
    $url = "http://ip-api.com/json/{$host}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
function getIpInfoWithRetry($host, $maxRetries = 3, $retryDelay = 1.5) {
    $retryCount = 0;

    while ($retryCount < $maxRetries) {
        $url = "http://ip-api.com/json/{$host}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 检查是否成功获取响应
        if ($httpCode == 200) {
            return $response; // 返回响应
        } else {
            // 请求失败，增加重试次数，并等待一段时间后重试
            $retryCount++;
            sleep($retryDelay); // 等待一段时间后重试
        }
    }

    return false; // 超过最大重试次数，返回失败
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['links'])) {
        $links = explode("\n", $_POST['links']);
        $linkIndex = 1; // 设置链接索引从1开始

        // 开始表格
        echo "<div class='table-container'>";
        echo "<table>";
        echo "<tr><th>序号</th><th>Host</th><th>IP</th><th>端口</th><th>国家</th><th>国家代码</th><th>城市</th><th>ISP</th><th>Org</th><th>备注</th></tr>";

        // 初始化文本框内容
        $textAreaContent = "";
        $rrr=[' INFORMATION TECHNOLOGY (HONGKONG) CO., LIMITED', ' Abr Arvan Co. ( Private Joint Stock)', ' INFORMATION TECHNOLOGY (HK) LIMITED', ' Posts and Telecommunications Group', ' T Broadband Public Company Limited', ' Bilisim Teknolojileri A.S.', ' communications corporation', ' Network Technology Co Ltd', ' (US) Technology Co., Ltd.', ' USA Shared Services Inc.', '(US) Technology Co., Ltd.', ' Communication Co., ltd.', ' (HK) Network Technology', ' Solutions Sdn. Bhd.', ' Digital Global Inc', ' Technologies Inc.', ' International LTD', ' International Ltd', ' Cloud HK Limited', ' Cloud Computing', ' Hosting Sdn Bhd', ' Private Limited', ' Japan Co., Ltd.', ' Communications', ' Cloud Services', ' Data Centers.', ' (HK) Network', ' AFRICA CLOUD', ' Networks Ltd', ' Networks Inc', ' Link Limited', ' Technologies', ' Corporation', ' Centers II', ' Enterprise', ' Labs S.A.', '.com, Inc.', ' Solutions', ' Pte. Ltd', ' TECH INC', ' Host Ltd', 'Zhejiang ', ' Sdn Bhd', ' Pty Ltd', ' Limited', ' Centers', '.com LLC', ' Hosting', ' NETWORK', ' Network', ' Online', ' Global', ', Inc.', ', inc.', ' Host.', ', Inc', ', LLC', ' Inc.', ' GmbH', ' LLC', ' Inc', ' LTD', '.com', ' SRL', ' Ltd'];
        // 解析所有的链接
        foreach ($links as $link) {
            $link = trim($link);
            if (!empty($link)) {
                $parsed_data = parseLink($link);

                // 根据协议类型打印不同的内容
                if ($parsed_data['protocol'] === 'vmess') {
                    $vmess_config = parseVMessLink($link);
                    if (!empty($vmess_config['add'])) {
                        $host = strtok($vmess_config['add'], ':');
                        $port=$vmess_config['port'];
                        // $ip_info = getIpInfoWithRetry($host);
                        $ip_info = getIpInfoWithRetry($host);

                        $ip_data = json_decode($ip_info, true);

                        $isp = $ip_data['isp'];
                        $countryCode = $ip_data['countryCode'];
                        $org = $ip_data['org'];
                        $remarks = $isp . '-' . $countryCode;

                        $remarks = str_replace(['Shenzhen Tencent Computer Systems Company Limited'],'Tencent',$remarks);
                        $remarks = str_replace(['Hangzhou Alibaba Advertising Co'],'Alibaba',$remarks);
                        $remarks = str_replace(['The Constant Company'],'Constant',$remarks);
                        $remarks = str_replace(['Jerng Yue Lee trading as Evoxt Enterprise'],'Evoxt',$remarks);

                        $remarks = str_replace($rrr, '', $remarks);





                        $vmess_config['ps'] = $remarks; // 设置 ps 为备注
                        $encoded_vmess_link = base64_encode(json_encode($vmess_config)); // 重新编码为 vmess 格式
                        echo "<tr><td>{$linkIndex}</td><td>{$host}</td><td>{$host}</td><td>{$port}</td><td>{$ip_data['country']}</td><td>{$ip_data['countryCode']}</td><td>{$ip_data['city']}</td><td>{$ip_data['isp']}</td><td>{$ip_data['org']}</td><td>{$remarks}</td></tr>";
                        // echo "<p>vmess://{$encoded_vmess_link}</p>";

                        // 添加链接到文本框内容
                        $textAreaContent .= "vmess://{$encoded_vmess_link}\n";
                    }
                } else {
                    if (!empty($parsed_data['host'])) {
                        $host = strtok($parsed_data['host'], ':'); // 去除 ":" 以及之后的内容
                        // $ip_info = getIpInfo($host);
                        $ip_info = getIpInfoWithRetry($host);
                        $port=$parsed_data['port'];
                        $ip_data = json_decode($ip_info, true);

                        $isp = $ip_data['isp'];
                        $countryCode = $ip_data['countryCode'];
                        $org = $ip_data['org'];
                        $remarks = $isp . '-' . $countryCode;

                        $remarks = str_replace(['Shenzhen Tencent Computer Systems Company Limited'],'Tencent',$remarks);
                        $remarks = str_replace(['Hangzhou Alibaba Advertising Co'],'Alibaba',$remarks);
                        $remarks = str_replace(['The Constant Company'],'Constant',$remarks);
                        $remarks = str_replace(['Jerng Yue Lee trading as Evoxt Enterprise'],'Evoxt',$remarks);

                        $remarks = str_replace($rrr, '', $remarks);




                        // 获取 '#' 之前的内容作为链接
                        $link_before_hash = strtok($link, '#');

                        // 将备注替换为原链接中 '#' 之后的内容
                        $link_with_remarks = $link_before_hash . '#' . $remarks;

                        // 添加相应的格式头部
                        $reconstructed_link = $parsed_data['protocol'] . '://' . $parsed_data['password'] . '@' . $parsed_data['host'] .
                                            ':' . $parsed_data['port'] . '/' . $parsed_data['path'] . '#' . $parsed_data['query'];

                        echo "<tr><td>{$linkIndex}</td><td>{$host}</td><td>{$host}</td><td>{$port}</td><td>{$ip_data['country']}</td><td>{$ip_data['countryCode']}</td><td>{$ip_data['city']}</td><td>{$ip_data['isp']}</td><td>{$ip_data['org']}</td><td>{$remarks}</td></tr>";
                        // echo "<p>{$link_with_remarks}</p>";

                        // 添加链接到文本框内容
                        $textAreaContent .= "{$link_with_remarks}\n";
}
}
}
$linkIndex++; // 递增链接索引
}
// 结束表格
echo "</table>";
echo "</div>";
    // 将链接显示到文本框中
    echo "<textarea id='linkTextArea' rows='10' cols='50'>$textAreaContent</textarea>";


    }
}
?>

<script>
function copyToClipboard() {
    var textarea = document.getElementById('linkTextArea');
    textarea.select();
    document.execCommand('copy');
    alert('链接已复制到剪贴板');
}
</script>
<footer>
    <div class="copyright">
        &copy; 2024 JIEMO. All rights reserved.
    </div>
    <div class="github">
        <a href="https://github.com/jiemo9527/HPWRYF" target="_blank">Jump to Github project</a>
    </div>
</footer>
<script>
    // 获取GitHub链接元素
    var githubLink = document.querySelector('.github a');
    // 打印GitHub链接地址到控制台
    console.log('GitHub地址：', githubLink.getAttribute('href'));

</script>
</div>
</body>
</html>
