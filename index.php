<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPWAYF解析链接</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.5/xlsx.full.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
        }

        .container {
            width: 84%;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow-y: auto;
            max-height: calc(100vh - 40px);
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
            box-sizing: border-box;
        }

        input[type="submit"], button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 5px;
        }

        input[type="submit"]:hover, button:hover {
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
            <textarea id="links" name="links" rows="10" cols="50" placeholder="请在此处粘贴链接，每行一个..."></textarea><br>
            <input type="submit" value="解析">
            <button type='button' onclick='copyToClipboard()'>复制Remark配置</button>
            <button type='button' onclick='exportResultsToExcel()'>导出为Excel</button>
        </form>
    </div>

<?php
// 解析 VMess 链接 (JSON格式)
function parseVMessLink($vmess_link) {
    $base64_part = str_replace('vmess://', '', $vmess_link);
    $decoded_link = base64_decode($base64_part);
    return json_decode($decoded_link, true);
}

// 解析 VLESS/Trojan/Socks 等通用格式链接
function parseGenericLink($link) {
    $parsed_data = array();
    // 注意：已从正则表达式中移除 ss 和 vmess
    if (preg_match('/^(vless|socks|trojan):\/\/([^:@]+):?([^@]*)@?([^\/#?]+):?([^\/]*)\/?([^#]*)#?(.*)$/', $link, $matches)) {
        $parsed_data['protocol'] = $matches[1];
        $host_and_port = explode(':', $matches[4]);
        $parsed_data['host'] = $host_and_port[0];
        $parsed_data['port'] = isset($host_and_port[1]) ? $host_and_port[1] : '';
    }
    return $parsed_data;
}

// 使用 cURL 获取 IP 地理位置信息，并带重试机制
function getIpInfoWithRetry($host, $maxRetries = 3, $retryDelay = 1) {
    $retryCount = 0;
    while ($retryCount < $maxRetries) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/{$host}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 设置超时
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 && $response) {
            return $response;
        } else {
            $retryCount++;
            sleep($retryDelay);
        }
    }
    return false;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['links'])) {
    $links = explode("\n", $_POST['links']);
    $linkIndex = 1;

    echo "<div class='table-container'>";
    echo "<table id='resultsTable'>";
    echo "<tr><th>序号</th><th>Host</th><th>IP</th><th>端口</th><th>国家</th><th>国家代码</th><th>城市</th><th>ISP</th><th>Org</th><th>备注</th></tr>";

    $textAreaContent = "";
    $rrr=[' INFORMATION TECHNOLOGY (HONGKONG) CO., LIMITED', ' Abr Arvan Co. ( Private Joint Stock)', ' INFORMATION TECHNOLOGY (HK) LIMITED', ' Posts and Telecommunications Group', ' T Broadband Public Company Limited', ' Bilisim Teknolojileri A.S.', ' communications corporation', ' Network Technology Co Ltd', ' (US) Technology Co., Ltd.', ' USA Shared Services Inc.', '(US) Technology Co., Ltd.', ' Communication Co., ltd.', ' (HK) Network Technology', ' Solutions Sdn. Bhd.', ' Digital Global Inc', ' Technologies Inc.', ' International LTD', ' International Ltd', ' Cloud HK Limited', ' Cloud Computing', ' Hosting Sdn Bhd', ' Private Limited', ' Japan Co., Ltd.', ' Communications', ' Cloud Services', ' Data Centers.', ' (HK) Network', ' AFRICA CLOUD', ' Networks Ltd', ' Networks Inc', ' Link Limited', ' Technologies', ' Corporation', ' Centers II', ' Enterprise', ' Labs S.A.', '.com, Inc.', ' Solutions', ' Pte. Ltd', ' TECH INC', ' Host Ltd', 'Zhejiang ', ' Sdn Bhd', ' Pty Ltd', ' Limited', ' Centers', '.com LLC', ' Hosting', ' NETWORK', ' Network', ' Online', ' Global', ', Inc.', ', inc.', ' Host.', ', Inc', ', LLC', ' Inc.', ' GmbH', ' LLC', ' Inc', ' LTD', '.com', ' SRL', ' Ltd'];

    foreach ($links as $link) {
        $link = trim($link);
        if (empty($link)) continue;

        $host = null;
        $port = null;
        $protocol_type = '';
        $original_data = null; // 用于存储原始解析数据以重建链接

        // --- **核心逻辑重构** ---
        // 根据不同协议类型进行解析
        if (strpos($link, 'vmess://') === 0) {
            $protocol_type = 'vmess';
            $parsed = parseVMessLink($link);
            if (!empty($parsed['add'])) {
                $host = $parsed['add'];
                $port = $parsed['port'];
                $original_data = $parsed;
            }
        } elseif (strpos($link, 'ss://') === 0) {
            $protocol_type = 'ss';
            $link_body = substr($link, 5); // 移除 'ss://'
            $parts = explode('#', $link_body, 2);
            $main_part = $parts[0];

            // 尝试解码 SIP002 格式: base64(method:pass@host:port)
            $decoded = base64_decode(rtrim($main_part, "=\r\n\t"), true);
            if ($decoded !== false && strpos($decoded, '@') !== false) {
                list(, $host_part) = explode('@', $decoded, 2);
                $host_port_parts = explode(':', $host_part);
                $host = $host_port_parts[0];
                $port = isset($host_port_parts[1]) ? $host_port_parts[1] : '';
            }
            // 兼容旧格式: base64(method:pass)@host:port
            elseif (strpos($main_part, '@') !== false) {
                list(, $host_part) = explode('@', $main_part, 2);
                $host_port_parts = explode(':', $host_part);
                $host = $host_port_parts[0];
                $port = isset($host_port_parts[1]) ? $host_port_parts[1] : '';
            }
        } else {
            // 处理 VLESS, Trojan 等其他类型
            $parsed = parseGenericLink($link);
            if (!empty($parsed['host'])) {
                $protocol_type = $parsed['protocol'];
                $host = $parsed['host'];
                $port = $parsed['port'];
            }
        }

        // 如果成功解析出 host，则执行后续操作
        if ($host) {
            $ip_info_json = getIpInfoWithRetry($host);
            $ip_data = $ip_info_json ? json_decode($ip_info_json, true) : [];

            // 安全地获取IP信息
            $country = $ip_data['country'] ?? 'N/A';
            $countryCode = $ip_data['countryCode'] ?? 'N/A';
            $city = $ip_data['city'] ?? 'N/A';
            $isp = $ip_data['isp'] ?? 'N/A';
            $org = $ip_data['org'] ?? 'N/A';

            // 生成备注
            $remarks = $org . '-' . $countryCode;
            $remarks = str_replace(['Shenzhen Tencent Computer Systems Company Limited'],'Tencent',$remarks);
            $remarks = str_replace(['Hangzhou Alibaba Advertising Co'],'Alibaba',$remarks);
            $remarks = str_replace(['The Constant Company'],'Constant',$remarks);
            $remarks = str_replace(['Jerng Yue Lee trading as Evoxt Enterprise'],'Evoxt',$remarks);
            $remarks = str_replace($rrr, '', $remarks);
            $remarks = trim($remarks, '- ');

            // 生成带新备注的链接
            $new_link = '';
            if ($protocol_type === 'vmess') {
                $original_data['ps'] = $remarks;
                $new_link = "vmess://" . base64_encode(json_encode($original_data));
            } else { // 适用于 ss, vless, trojan 等
                $link_before_hash = strtok($link, '#');
                $new_link = $link_before_hash . '#' . urlencode($remarks); // 对备注进行URL编码，防止特殊字符问题
            }

            $textAreaContent .= $new_link . "\n";

            // 在表格中显示结果
            echo "<tr><td>{$linkIndex}</td><td>{$host}</td><td>{$host}</td><td>{$port}</td><td>{$country}</td><td>{$countryCode}</td><td>{$city}</td><td>{$isp}</td><td>{$org}</td><td>{$remarks}</td></tr>";
            $linkIndex++;
        }
    } // end foreach

    echo "</table>";
    echo "</div>";

    // 将生成的新链接显示在文本框中
    echo "<textarea id='linkTextArea' rows='10' cols='50'>$textAreaContent</textarea>";
}
?>

<script>
// 用于复制 Remark 配置的函数
function copyToClipboard() {
    var textarea = document.getElementById('linkTextArea');
    if (!textarea || !textarea.value) {
        alert('没有可复制的内容。');
        return;
    }
    textarea.select();
    document.execCommand('copy');
    alert('链接已复制到剪贴板');
}

// --- 表格导出为Excel的功能 ---

// 1. 生成文件名
function generateRandomFileName() {
    const date = new Date();
    const dateStr = `${(date.getMonth() + 1).toString().padStart(2, '0')}${date.getDate().toString().padStart(2, '0')}`;
    return `${document.title || 'Export'}_${dateStr}.xlsx`;
}

// 2. 导出结果表格为Excel
function exportResultsToExcel() {
    const table = document.getElementById('resultsTable');
    if (!table || table.rows.length <= 1) { // 检查表格是否存在且有数据行
        alert('未找到可导出的数据。请先解析链接生成表格。');
        return;
    }
    const data = Array.from(table.rows).map(row =>
        Array.from(row.cells).map(cell => cell.innerText.trim())
    );
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, '解析结果');
    XLSX.writeFile(wb, generateRandomFileName());
}
</script>
<footer>
    <div class="copyright">
        &copy; 2024-2025 JIEMO. All rights reserved.
    </div>
    <div class="github">
        <a href="https://github.com/jiemo9527/HPWRYF" target="_blank">Jump to Github project</a>
    </div>
</footer>
</div>
</body>
</html>