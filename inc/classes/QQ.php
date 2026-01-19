<?php

namespace Sakura\API;

class QQ
{
    public static function get_qq_info($qq) {
        // Validate QQ number: must be 3 or more digits
        if (!preg_match('/^\d{3,}$/', $qq)) {
            return array(
                'status' => 400,
                'success' => false,
                'message' => 'Invalid QQ number.'
            );
        }
        $api_key = iro_opt('qq_avatar_api_key');
        $query = http_build_query([
            'key' => $api_key,
            'qq' => $qq
        ]);
        $url = 'https://api.nsmao.net/api/qq/v1/query?' . $query;
        $get_info = file_get_contents($url);
        $name = json_decode($get_info, true);
        if ($name) {
            if ($name['code'] == 200){
                $output = array(
                    'status' => 200,
                    'success' => true,
                    'message' => 'success',
                    'avatar' => $name['data']['avatar'],
                    'name' => $name['data']['name'],
                );
            }
        } else {
            $output = array(
                'status' => 404,
                'success' => false,
                'message' => 'QQ number not exist.'
            );
        }
        return $output;
    }

    public static function get_qq_avatar($encrypted) {
        global $sakura_privkey;
        if (isset($encrypted)) {
            $decoded = base64_decode(urldecode($encrypted));
            $iv = substr($decoded, 0, 16); // 提取前16字节作为IV
            $data = substr($decoded, 16); // 剩余是加密数据
            $qq_number = openssl_decrypt($data, 'aes-128-cbc', $sakura_privkey, 0, $iv);

            preg_match('/^\d{3,}$/', $qq_number, $matches);
            return 'https://q2.qlogo.cn/headimg_dl?dst_uin=' . $matches[0] . '&spec=100';
        }
    }
}
