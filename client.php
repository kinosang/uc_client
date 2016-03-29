<?php

/**
 * 纯 HTTP 版 Ucenter client
 * 基于 UC_Client 1.6.0 (20141101)
 * 可以直接替换官方原版 client 使用
 * 实现常用接口
 * 具体接口见下方代码
 */

if (!defined('UC_API')) {
    exit('Access denied');
}

error_reporting(0);

define('IN_UC', true);
define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20141101');

require_once 'xml.class.php';

/* 登录接口 */
function uc_user_login($username, $password, $isuid = 3, $checkques = 0, $questionid = '', $answer = '', $ip = '')
{
    $username = urlencode(uc_charset($username));
    $password = urlencode($password);

    $s = uc_http_request('user', 'login', 'username=' . $username . '&password=' . $password . '&isuid=' . $isuid . '&checkques=' . $checkques . '&questionid=' . $questionid . '&answer=' . $answer . '&ip=' . $ip);

    if (strpos($s, 'Access denied for agent changed') !== false) {
        return '可能 APPKEY 没有设置正确。';
    }

    $arr = xml_unserialize($s);

    if (is_array($arr)) {
        $arr += array(
            'status'   => $arr[0],
            'username' => uc_charset($arr[1], 0),
            'password' => $arr[2],
            'email'    => $arr[3],
            'merge'    => $arr[4],
        );
        return $arr;
    } else {
        return $arr;
    }
}

/* 短消息接口 */
function uc_pm_checknew($uid, $more = 0)
{
    return uc_http_request('pm', 'check_newpm', 'uid=' . $uid . '&more=' . $more);
}

function uc_pm_list($uid, $page = 1, $pagesize = 10, $folder = 'inbox', $filter = 'newpm', $msglen = 0)
{
    return uc_http_request('pm', 'ls', 'uid=' . $uid . '&page=' . $page . '&pagesize=' . $pagesize . '&folder=' . $folder . '&filter=' . $filter . '&msglen=' . $msglen);
}

function uc_pm_location($uid, $newpm = 0)
{
    $apiurl = uc_http_url('pm_client', 'ls', 'uid=' . $uid);
    @header('Expires: 0');
    @header('Cache-Control: private, post-check=0, pre-check=0, max-age=0', false);
    @header('Pragma: no-cache');
    @header('Location: ' . $apiurl);
}

function uc_pm_send($fromuid, $msgto, $subject, $message, $instantly = 1, $replypmid = 0, $isusername = 0, $type = 0)
{
    return uc_http_request('pm', 'sendpm', 'fromuid=' . $fromuid . '&msgto=' . $msgto . '&subject=' . $subject . '&message=' . $message . '&replypmid=' . $replypmid . '&isusername=' . $isusername . '&type=' . $type);
}

function uc_pm_blackls_get($uid)
{
    return uc_http_request('pm', 'blackls_get', 'uid=' . $uid);
}

function uc_pm_blackls_set($uid, $blackls)
{
    return uc_http_request('pm', 'blackls_set', 'uid=' . $uid . '&blackls=' . $blackls);
}

function uc_pm_blackls_add($uid, $username)
{
    return uc_http_request('pm', 'blackls_add', 'uid=' . $uid . '&username=' . $username);
}

function uc_pm_blackls_delete($uid, $username)
{
    return uc_http_request('pm', 'blackls_delete', 'uid=' . $uid . '&username=' . $username);
}

/* 好友接口 */
function uc_friend_totalnum($uid, $direction = 0)
{
    return uc_http_request('friend', 'totalnum', 'uid=' . $uid . '&direction=' . $direction);
}

function uc_friend_ls($uid, $page = 1, $pagesize = 10, $totalnum = 10, $direction = 0)
{
    return xml_unserialize(uc_http_request('friend', 'ls', 'uid=' . $uid . '&page=' . $page . '&pagesize=' . $pagesize . '&totalnum=' . $totalnum . '&direction=' . $direction));
}

function uc_friend_add($uid, $friendid, $comment = '')
{
    return uc_http_request('friend', 'add', 'uid=' . $uid . '&friendid=' . $friendid . '&comment=' . $comment);
}

function uc_friend_delete($uid, $friendids)
{
    return uc_http_request('friend', 'delete', 'uid=' . $uid . '&friendids=' . implode(',', $friendids));
}

// 头像接口
function uc_avatar($uid, $type = 'virtual', $returnhtml = 1)
{
    $input          = urlencode(uc_authcode('uid=' . $uid . '&agent=' . md5($_SERVER['HTTP_USER_AGENT']) . '&time=' . time(), 'ENCODE', UC_KEY));
    $uc_avatarflash = UC_API . '/images/camera.swf?inajax=1&appid=' . UC_APPID . '&input=' . $input . '&agent=' . md5($_SERVER['HTTP_USER_AGENT']) . '&ucapi=' . UC_API . '&avatartype=' . $type . '&uploadSize=2048';
    if ($returnhtml) {
        return '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="450" height="253" id="mycamera" align="middle">
                <param name="allowScriptAccess" value="always" />
                <param name="scale" value="exactfit" />
                <param name="wmode" value="transparent" />
                <param name="quality" value="high" />
                <param name="bgcolor" value="#ffffff" />
                <param name="movie" value="' . $uc_avatarflash . '" />
                <param name="menu" value="false" />
                <embed src="' . $uc_avatarflash . '" quality="high" bgcolor="#ffffff" width="450" height="253" name="mycamera" align="middle" allowScriptAccess="always" allowFullScreen="false" scale="exactfit"  wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
            </object>';
    } else {
        return array(
            'width', '450',
            'height', '253',
            'scale', 'exactfit',
            'src', $uc_avatarflash,
            'id', 'mycamera',
            'name', 'mycamera',
            'quality', 'high',
            'bgcolor', '#ffffff',
            'menu', 'false',
            'swLiveConnect', 'true',
            'allowScriptAccess', 'always',
        );
    }
}

/**
 * 同步登录：
 * 返回一个 HTML 内容， script 标签 调用各 client
 */
function uc_user_synlogin($uid)
{
    return uc_http_request('user', 'synlogin', 'uid=' . $uid);
    //preg_match_all('#<script type="text/javascript" src="([^"]+")#is', $s, $m);
    //return isset($m[1]) ? $m[1] : $s;
}

/**
 *同步退出：同上
 */
function uc_user_synlogout()
{
    return uc_http_request('user', 'synlogout', '');
    //preg_match_all('#<script type="text/javascript" src="([^"]+)"#is', $s, $m);
    //return isset($m[1]) ? $m[1] : $s;
}

// 修改密码
function uc_user_updatepw($username, $newpw)
{
    $username = urlencode(uc_charset($username));
    $newpw    = urlencode($newpw);
    return uc_http_request('user', 'edit', 'username=' . $username . '&newpw=' . $newpw . '&ignoreoldpw=1');
}

// 删除用户
function uc_user_delete($uid)
{
    return uc_http_request('user', 'delete', 'uid=' . $uid);
}

// 注册
function uc_user_register($username, $password, $email)
{
    $username = urlencode(uc_charset($username));
    $password = urlencode($password);
    $email    = urlencode($email);
    $regip    = $_SERVER['REMOTE_ADDR'];
    return uc_http_request('user', 'register', 'username=' . $username . '&password=' . $password . '&email=' . $email . '&ip=' . $regip);
}

// 根据用户名获取一个用户
function uc_get_user($username)
{
    $username = urlencode(uc_charset($username));
    $s        = uc_http_request('user', 'get_user', 'username=' . $username . '&isuid=0');
    $arr      = xml_unserialize($s);
    if (is_array($arr)) {
        $arr += array(
            'uid'      => $arr[0],
            'username' => uc_charset($arr[1], 0),
            'email'    => $arr[2],
        );
        return $arr;
    } else {
        return $s;
    }
}

// UTF-8 与 uc 编码互转函数， $to 控制转换方向。
function uc_charset($s, $to = 1)
{
    return UC_CHARSET == 'UTF-8' ? $s : iconv($to ? 'UTF-8' : UC_CHARSET, $to ? UC_CHARSET : 'UTF-8', $s);
}

// 返回 ucenter 服务端 api 调用 url
function uc_http_url($module, $action, $arg = '')
{
    $input = urlencode(uc_authcode($arg . '&agent=' . md5($_SERVER['HTTP_USER_AGENT']) . '&time=' . time(), 'ENCODE', UC_KEY));
    return UC_API . ((substr(UC_API, -1) != '/') ? '/' : '') . 'index.php?' . 'm=' . $module . '&a=' . $action . '&inajax=2&release=' . UC_CLIENT_RELEASE . '&input=' . $input . '&appid=' . UC_APPID;
}

// 执行 ucenter 服务端 api 调用
function uc_http_request($module, $action, $arg = '')
{
    $url = uc_http_url($module, $action, $arg);
    return xn_get_url($url, 5);
}

// RC4 加密
function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    $ckey_length = 4;

    $key  = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey   = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box    = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j       = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp     = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a       = ($a + 1) % 256;
        $j       = ($j + $box[$a]) % 256;
        $tmp     = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

// http 请求
function xn_get_url($url, $timeout = 5, $post = '', $cookie = '')
{
    if (function_exists('curl_init')) {
        $curl = curl_init();

        if (!empty($post)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        $cookie = array();
        foreach ($_COOKIE as $key => $value) {
            $cookie[] = $key . '=' . $value;
        }
        ;

        $cookie = implode('; ', $cookie);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    } else {
        return null;
    }
}

/* xml 处理 */
function xml_unserialize($xml, $isnormal = false)
{
    $xml_parser = new XML($isnormal);
    $data       = $xml_parser->parse($xml);
    $xml_parser->destruct();
    return $data;
}

function xml_serialize($arr, $htmlon = false, $isnormal = false, $level = 1)
{
    $s     = $level == 1 ? "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n<root>\r\n" : '';
    $space = str_repeat("\t", $level);
    foreach ($arr as $k => $v) {
        if (!is_array($v)) {
            $s .= $space . "<item id=\"$k\">" . ($htmlon ? '<![CDATA[' : '') . $v . ($htmlon ? ']]>' : '') . "</item>\r\n";
        } else {
            $s .= $space . "<item id=\"$k\">\r\n" . xmlSerialize($v, $htmlon, $isnormal, $level + 1) . $space . "</item>\r\n";
        }
    }
    $s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
    return $level == 1 ? $s . "</root>" : $s;
}
