<?php

if (!defined('UC_API')) {
    exit('Access denied');
}

error_reporting(0);

define('IN_UC', true);
define('UC_CLIENT_VERSION', '1.6.0');
define('UC_CLIENT_RELEASE', '20141101');

require_once 'xml.class.php';

/* 用户接口 */

// 用户注册
function uc_user_register($username, $password, $email)
{
    $username = urlencode(uc_charset($username));
    $password = urlencode($password);
    $email    = urlencode($email);
    $regip    = $_SERVER['REMOTE_ADDR'];
    return uc_http_request('user', 'register', 'username=' . $username . '&password=' . $password . '&email=' . $email . '&ip=' . $regip);
}

// 用户登录
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

// 获取用户数据
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

// 更新用户资料
function uc_user_edit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $questionid = '', $answer = '')
{
    return uc_http_request('user', 'edit', array('username' => $username, 'oldpw' => $oldpw, 'newpw' => $newpw, 'email' => $email, 'ignoreoldpw' => $ignoreoldpw, 'questionid' => $questionid, 'answer' => $answer));
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

/**
 * 同步登录
 * 返回一个 HTML 内容， script 标签 调用各 client
 */
function uc_user_synlogin($uid)
{
    return uc_http_request('user', 'synlogin', 'uid=' . $uid);
    //preg_match_all('#<script type="text/javascript" src="([^"]+")#is', $s, $m);
    //return isset($m[1]) ? $m[1] : $s;
}

// 同步退出
function uc_user_synlogout()
{
    return uc_http_request('user', 'synlogout', '');
    //preg_match_all('#<script type="text/javascript" src="([^"]+)"#is', $s, $m);
    //return isset($m[1]) ? $m[1] : $s;
}

// 检查 Email 地址
function uc_user_checkemail($email)
{
    return uc_http_request('user', 'check_email', array('email' => $email));
}

// 检查用户名
function uc_user_checkname($username)
{
    return uc_http_request('user', 'check_username', array('username' => $username));
}

// 添加保护用户
function uc_user_addprotected($username, $admin = '')
{
    return uc_http_request('user', 'addprotected', array('username' => $username, 'admin' => $admin));
}

// 删除保护用户
function uc_user_deleteprotected($username)
{
    return uc_http_request('user', 'deleteprotected', array('username' => $username));
}

// 得到受保护的用户名列表
function uc_user_getprotected()
{
    return uc_http_request('user', 'getprotected', array('1' => 1));
}

// 把重名用户合并到 UCenter
function uc_user_merge($oldusername, $newusername, $uid, $password, $email)
{
    return uc_http_request('user', 'merge', array('oldusername' => $oldusername, 'newusername' => $newusername, 'uid' => $uid, 'password' => $password, 'email' => $email));
}

// 取消用户合并
function uc_user_merge_remove($username)
{
    return uc_http_request('user', 'merge_remove', array('username' => $username));
}

/* 短消息接口 */

// 进入短消息中心
function uc_pm_location($uid, $newpm = 0)
{
    $apiurl = uc_http_url('pm_client', 'ls', 'uid=' . $uid);
    @header('Expires: 0');
    @header('Cache-Control: private, post-check=0, pre-check=0, max-age=0', false);
    @header('Pragma: no-cache');
    @header('Location: ' . $apiurl);
}

// 检查新的短消息
function uc_pm_checknew($uid, $more = 0)
{
    return uc_http_request('pm', 'check_newpm', 'uid=' . $uid . '&more=' . $more);
}

// 发送短消息
function uc_pm_send($fromuid, $msgto, $subject, $message, $instantly = 1, $replypmid = 0, $isusername = 0, $type = 0)
{
    return uc_http_request('pm', 'sendpm', 'fromuid=' . $fromuid . '&msgto=' . $msgto . '&subject=' . $subject . '&message=' . $message . '&replypmid=' . $replypmid . '&isusername=' . $isusername . '&type=' . $type);
}

// 删除短消息
function uc_pm_delete($uid, $folder, $pmids)
{
    return uc_http_request('pm', 'delete', array('uid' => $uid, 'pmids' => $pmids));
}

// 获取短消息列表
function uc_pm_list($uid, $page = 1, $pagesize = 10, $folder = 'inbox', $filter = 'newpm', $msglen = 0)
{
    return uc_http_request('pm', 'ls', 'uid=' . $uid . '&page=' . $page . '&pagesize=' . $pagesize . '&folder=' . $folder . '&filter=' . $filter . '&msglen=' . $msglen);
}

// 忽略未读消息提示
function uc_pm_ignore($uid)
{
    return uc_http_request('pm', 'ignore', array('uid' => intval($uid)));
}

// 获取短消息内容
function uc_pm_view($uid, $pmid = 0, $touid = 0, $daterange = 1, $page = 0, $pagesize = 10, $type = 0, $isplid = 0)
{
    $uid      = intval($uid);
    $touid    = intval($touid);
    $page     = intval($page);
    $pagesize = intval($pagesize);
    $pmid     = @is_numeric($pmid) ? $pmid : 0;
    return uc_http_request('pm', 'view', array('uid' => $uid, 'pmid' => $pmid, 'touid' => $touid, 'daterange' => $daterange, 'page' => $page, 'pagesize' => $pagesize, 'type' => $type, 'isplid' => $isplid));
}

// 获取单条短消息内容
function uc_pm_viewnode($uid, $type, $pmid)
{
    $uid  = intval($uid);
    $type = intval($type);
    $pmid = @is_numeric($pmid) ? $pmid : 0;
    return uc_http_request('pm', 'viewnode', array('uid' => $uid, 'type' => $type, 'pmid' => $pmid));
}

// 获取黑名单
function uc_pm_blackls_get($uid)
{
    return uc_http_request('pm', 'blackls_get', 'uid=' . $uid);
}

// 更新黑名单
function uc_pm_blackls_set($uid, $blackls)
{
    return uc_http_request('pm', 'blackls_set', 'uid=' . $uid . '&blackls=' . $blackls);
}

// 添加黑名单条目
function uc_pm_blackls_add($uid, $username)
{
    return uc_http_request('pm', 'blackls_add', 'uid=' . $uid . '&username=' . $username);
}

// 删除黑名单条目
function uc_pm_blackls_delete($uid, $username)
{
    return uc_http_request('pm', 'blackls_delete', 'uid=' . $uid . '&username=' . $username);
}

/* 好友接口 */

// 添加好友
function uc_friend_add($uid, $friendid, $comment = '')
{
    return uc_http_request('friend', 'add', 'uid=' . $uid . '&friendid=' . $friendid . '&comment=' . $comment);
}

// 删除好友
function uc_friend_delete($uid, $friendids)
{
    return uc_http_request('friend', 'delete', 'uid=' . $uid . '&friendids=' . implode(',', $friendids));
}

// 获取好友总数
function uc_friend_totalnum($uid, $direction = 0)
{
    return uc_http_request('friend', 'totalnum', 'uid=' . $uid . '&direction=' . $direction);
}

// 获取好友列表
function uc_friend_ls($uid, $page = 1, $pagesize = 10, $totalnum = 10, $direction = 0)
{
    return xml_unserialize(uc_http_request('friend', 'ls', 'uid=' . $uid . '&page=' . $page . '&pagesize=' . $pagesize . '&totalnum=' . $totalnum . '&direction=' . $direction));
}

/* 头像接口 */

// 修改头像
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

/* 标签接口 */

// 获取标签数据
function uc_tag_get($tagname, $nums = 0)
{
    return uc_http_request('tag', 'gettag', array('tagname' => $tagname, 'nums' => $nums));
}

/* 事件接口 */

// 添加事件
function uc_feed_add($icon, $uid, $username, $title_template = '', $title_data = '', $body_template = '', $body_data = '', $body_general = '', $target_ids = '', $images = array())
{
    return uc_http_request('feed', 'add',
        array(
            'icon'           => $icon,
            'appid'          => UC_APPID,
            'uid'            => $uid,
            'username'       => $username,
            'title_template' => $title_template,
            'title_data'     => $title_data,
            'body_template'  => $body_template,
            'body_data'      => $body_data,
            'body_general'   => $body_general,
            'target_ids'     => $target_ids,
            'image_1'        => $images[0]['url'],
            'image_1_link'   => $images[0]['link'],
            'image_2'        => $images[1]['url'],
            'image_2_link'   => $images[1]['link'],
            'image_3'        => $images[2]['url'],
            'image_3_link'   => $images[2]['link'],
            'image_4'        => $images[3]['url'],
            'image_4_link'   => $images[3]['link'],
        )
    );
}

// 获取事件
function uc_feed_get($limit = 100, $delete = true)
{
    return uc_http_request('feed', 'get', array('limit' => $limit, 'delete' => $delete));
}

/* 应用接口 */

// 获取应用列表
function uc_app_ls()
{
    return uc_http_request('app', 'ls');
}

/* 内部函数 */

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
function uc_http_request($module, $action, $arg = [])
{
    $arg = is_string($arg) ? $arg : http_build_query($arg);
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
