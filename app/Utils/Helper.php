<?php


namespace App\Utils;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;


class Helper
{
    /**
     * 验证是否是手机号码
     * @param string $mobilephone
     * @return bool 正确返回true
     */
    static function isMobile(string $mobilephone)
    {
        return (preg_match("/^1[0-9]{10}$/", $mobilephone)) ? true : false;
    }

    /**
     * 验证是否是邮箱
     * @param string $address
     * @return bool
     */
    static function isEmail(string $address)
    {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? false : true;
    }

    /**
     * 获得随机字符串
     * @param int $len 长度
     * @param string $charset 验证码字符
     * @return string
     */
    static function getNonceStr(int $len, string $charset = 'abcdefghijklmnoprstuvwxyzABCDEFGHIJKLMNOPRSTUVWXYZ1234567890')
    {
        $code = '';
        $_len = strlen($charset) - 1;
        for ($i = 0; $i < $len; $i++) {
            $code .= $charset[mt_rand(0, $_len)];
        }

        return $code;
    }


    /**
     * 获得文本
     * @param $em 枚举值
     * @param $val 枚举值，为空默认返回所有
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed|string
     */
    public static function enumText($em, $val = '_not_key_')
    {
        $e = config('enum.' . $em, []);
        if ($val != '_not_key_') {
            return isset($e[$val]) ? $e[$val] : '未知';
        } else {
            return $e;
        }
    }

    /**
     * 获得值
     * @param $em 枚举值
     * @param $val 枚举值，为空默认返回所有
     * @return false|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|int|mixed|string
     */
    public static function enumValue($em, $val = '_not_key_')
    {
        $e = config('enum.' . $em, []);
        if ($val != '_not_key_') {
            return ($key = array_search($val, $e)) ? $key : '未知';
        } else {
            return $e;
        }
    }

    /**
     * 代理信息
     * @return \stdClass
     */
    public static function agent()
    {
        $agent = new \stdClass;

        //  获取ip
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } else if (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');

        } else if (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $agent->ip = empty($ip) ? null : $ip;

        // 获取当前请求的 User-Agent: 头部的内容。
        $agent->agents = $_SERVER['HTTP_USER_AGENT']??'';

        // 利用正则表达式匹配以上字符串，用户的浏览器操作系统信息。

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $client = strtolower($_SERVER['HTTP_USER_AGENT']);
            if (strpos($client, 'windows nt')) {
                $client = 2; // PC
            } else if (strpos($client, 'iphone')) {
                $client = 1; // 手机 iphone
            } else if (strpos($client, 'ipad')) {
                $client = 3; // pad
            } else if (strpos($client, 'android')) {
                $client = 1; // 手机 android
            }
        }

        $agent->client = empty($client) ?: 1; // 默认
        $os = '';

        // 获得访客操作系统
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $os = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/win/i', $os) && strpos($os, '95')) {
                $os = 'Windows 95';
            } else if (preg_match('/win 9x/i', $os) && strpos($os, '4.90')) {
                $os = 'Windows ME';
            } else if (preg_match('/win/i', $os) && preg_match('/98/i', $os)) {
                $os = 'Windows 98';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 6.0/i', $os)) {
                $os = 'Windows Vista';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 6.1/i', $os)) {
                $os = 'Windows 7';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 6.2/i', $os)) {
                $os = 'Windows 8';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 10.0/i', $os)) {
                $os = 'Windows 10';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 5.1/i', $os)) {
                $os = 'Windows XP';
            } else if (preg_match('/win/i', $os) && preg_match('/nt 5/i', $os)) {
                $os = 'Windows 2000';
            } else if (preg_match('/win/i', $os) && preg_match('/nt/i', $os)) {
                $os = 'Windows NT';
            } else if (preg_match('/win/i', $os) && preg_match('/32/i', $os)) {
                $os = 'Windows 32';
            } else if (preg_match('/linux/i', $os)) {
                $os = 'Linux';
            } else if (preg_match('/unix/i', $os)) {
                $os = 'Unix';
            } else if (preg_match('/sun/i', $os) && preg_match('/os/i', $os)) {
                $os = 'SunOS';
            } else if (preg_match('/ibm/i', $os) && preg_match('/os/i', $os)) {
                $os = 'IBM OS/2';
            } else if (preg_match('/Mac/i', $os) && preg_match('/PC/i', $os)) {
                $os = 'Macintosh';
            } else if (preg_match('/PowerPC/i', $os)) {
                $os = 'PowerPC';
            } else if (preg_match('/AIX/i', $os)) {
                $os = 'AIX';
            } else if (preg_match('/HPUX/i', $os)) {
                $os = 'HPUX';
            } else if (preg_match('/NetBSD/i', $os)) {
                $os = 'NetBSD';
            } else if (preg_match('/BSD/i', $os)) {
                $os = 'BSD';
            } else if (preg_match('/OSF1/i', $os)) {
                $os = 'OSF1';
            } else if (preg_match('/IRIX/i', $os)) {
                $os = 'IRIX';
            } else if (preg_match('/FreeBSD/i', $os)) {
                $os = 'FreeBSD';
            } else if (preg_match('/teleport/i', $os)) {
                $os = 'teleport';
            } else if (preg_match('/flashget/i', $os)) {
                $os = 'flashget';
            } else if (preg_match('/webzip/i', $os)) {
                $os = 'webzip';
            } else if (preg_match('/offline/i', $os)) {
                $os = 'offline';
            } else {
                $os = 'unknow';
            }
        }

        $agent->os = $os;

        $browser = '';
        $browser_ver = '';

        // 获得访问者浏览器
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];

            if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $br, $regs)) {
                $browser = 'OmniWeb';
                $browser_ver = $regs[2];
            } else if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $br, $regs)) {
                $browser = 'Netscape';
                $browser_ver = $regs[2];
            } else if (preg_match('/safari\/([^\s]+)/i', $br, $regs)) {
                $browser = 'Safari';
                $browser_ver = $regs[1];
            } else if (preg_match('/MSIE\s([^\s|;]+)/i', $br, $regs)) {
                $browser = 'Internet Explorer';
                $browser_ver = $regs[1];
            } else if (preg_match('/Opera[\s|\/]([^\s]+)/i', $br, $regs)) {
                $browser = 'Opera';
                $browser_ver = $regs[1];
            } else if (preg_match('/NetCaptor\s([^\s|;]+)/i', $br, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
                $browser_ver = $regs[1];
            } else if (preg_match('/Maxthon/i', $br, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
                $browser_ver = '';
            } else if (preg_match('/360SE/i', $br, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') 360SE';
                $browser_ver = '';
            } else if (preg_match('/SE 2.x/i', $br, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') 搜狗';
                $browser_ver = '';
            } else if (preg_match('/FireFox\/([^\s]+)/i', $br, $regs)) {
                $browser = 'FireFox';
                $browser_ver = $regs[1];
            } else if (preg_match('/Lynx\/([^\s]+)/i', $br, $regs)) {
                $browser = 'Lynx';
                $browser_ver = $regs[1];
            } else if (preg_match('/Chrome\/([^\s]+)/i', $br, $regs)) {
                $browser = 'Chrome';
                $browser_ver = $regs[1];
            } else if ($browser == '') {
                $browser = 'unknow';
                $browser_ver = 'unknow';
            }
        }

        $agent->br = $browser;
        $agent->br_ver = $browser_ver;

        //获得访问者浏览器语言
        if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $lang = substr($lang, 0, 5);
            if (preg_match('/zh-cn/i', $lang)) {
                $lang = '简体中文';
            } else if (preg_match('/zh/i', $lang)) {
                $lang = '繁体中文';
            } else {
                $lang = 'English';
            }
        } else {
            $lang = 'unknow';
        }

        $agent->lang = $lang;

        return $agent;
    }

    /**
     * 生成密码
     * @param string $password 密码
     * @return string
     */
    public static function createPassword(string $password)
    {
        return hash_hmac(config('app.password.HASH_HMAC_ALGO'), $password, config('app.password.HASH_HMAC_KEY'));
    }

    /**
     * 计算年龄
     * @param $birth
     * @return int|mixed|string
     */
    public static function getAge($birth)
    {
        [
            $birthYear,
            $birthMonth,
            $birthDay,
        ] = explode('-', date('Y-m-d', $birth));
        [
            $currentYear,
            $currentMonth,
            $currentDay,
        ] = explode('-', date('Y-m-d'));

        $age = $currentYear - $birthYear - 1;

        if ($currentMonth > $birthMonth || $currentMonth == $birthMonth && $currentDay >= $birthDay)
            $age++;
        return $age < 0 ? 0 : $age;
    }





    /**
     * 检查是否是视频
     * @param string $str
     * @return bool
     */
    public static function isVideo(string $str)
    {
        $videoType = [
            'avi',
            'wmv',
            'mpeg',
            'mp4',
            'm4v',
            'mov',
            'asf',
            'flv',
            'f4v',
            'rmvb',
            'rm',
            '3gp',
            'vob',
        ];

        [
            $name,
            $type,
        ] = explode('.', $str);
        return in_array(strtolower($type), $videoType);
    }

    /**
     * 内部状态普遍写法
     * @param array $arr 关键数组
     * @param string|null $key 数组key,
     * @param string|null $default 没有这个key的时候返回值
     * @param string $format 可以传
     * arr   => 直接返回数组
     * app   => 返回app通用格式
     * check => 检查这个key是否存在
     * getKey => 如果这个key不存在,那么返回数组的第一个key
     * @return array|mixed|string|null|bool
     */
    public static function getSelectFormat(array $arr, string $key = null, string $default = null, string $format = 'arr')
    {
        if ($key === null) {
            if ($format == 'app') {
                return Helper::getAppSelectFormat($arr);
            } else {
                return $arr;
            }
        } else {
            if ($format == 'check') {
                return isset($arr[$key]);
            } else if ($format == 'getKey') {
                return isset($arr[$key]) ? $key : $default;
            } else {
                return $arr[$key] ?? null;
            }

        }
    }


    /**
     * 获取app选择框格式 常用于状态等
     * @param array $data
     * @return array
     */
    public static function getAppSelectFormat(array $data): array
    {

        $return = [];

        foreach ($data as $key => $val) {
            $return[$key] = [
                'value' => $key,
                'name' => $val,
            ];
        }

        return $return;


    }


    /**
     * 获取app选择框格式 常用于状态等
     * @param array $data
     * @return array
     */
    public static function getAppSelectSortFormat(array $data): array
    {
        $return = array_map(function ($key, $val) {
            return [
                'value' => $key,
                'name' => $val,
            ];
        }, array_keys($data), $data);

        return $return;


    }

    /**
     *  获取app选择框格式 常用于用get()->toArray()出来的数据
     * @param array $data
     * @param string $key
     * @param string $value
     * @return array
     */
    public static function getAppListSelectFormat(array $data , $key='id' , $value = 'name'): array
    {
        $return = [];
        if(!empty($data)){
            foreach ($data as $val) {
                $return[] = [
                    'value' => $val[$key],
                    'name' => $val[$value],
                ];
            }
        }


        return $return;
    }




    /**
     * 判断是不是json
     * @param $string
     * @return bool
     */
    public static function isJson($string) {
        if(empty($string) || !is_string($string)){
            return false;
        }

        try
        {
            //校验json格式
            json_decode($string, true);
            return JSON_ERROR_NONE === json_last_error();

        } catch (\Exception $e)
        {
            report($e);
            return false;
        }
    }

    /**
     * 中文字符串中间部分替换（最多替换二分之一）加星号
     * @param string $username 中文字符串
     * @return string 处理后的字符串
     */
    public static function substrCut($username){
        // 计算字符串长度，无论汉字还是英文字符全部为1
        $length = mb_strlen($username,'utf-8');

        // 截取第一部分代码
        $firstStr1 = mb_substr($username,0,ceil($length/4),'utf-8');
        // 截取中间部分代码
        $firstStr = mb_substr($username,ceil($length/4),floor($length/2),'utf-8');
        // （方法一）截取剩余字符串
        $firstStr2 = mb_substr($username,ceil($length/4) + floor($length/2), floor(($length+1)/2 - 1),'utf-8');

        return $firstStr1 . str_repeat("*",mb_strlen($firstStr,'utf-8')) . $firstStr2;
    }



    /**
     * 高精度乘法
     * @param $one
     * @param $two
     * @param $rightOperand
     * @return string
     */
    public static function getBcmul($one , $two , $rightOperand = 2)
    {
        return bcmul($one , $two , $rightOperand);
    }


    /**
     * 多维数组去重
     * @param $arr
     * @return array
     */
    public static  function arrayUniqueMany($arr) {
        $t = array_map('serialize', $arr);
        //利用serialize()方法将数组转换为以字符串形式的一维数组
        $t = array_unique($t);
        //去掉重复值
        $new_arr = array_map('unserialize', $t);
        //然后将刚组建的一维数组转回为php值
        return $new_arr;
    }


    /**
     *
     * $lockReturn = Helper::routeLock('runPayGo' , Auth::id());
     * if($lockReturn['code'] != Response::HTTP_OK){
     * return ResponseHelper::sendAutoResponse(...array_values($lockReturn));
     * }
     *
     * 路由并发锁
     * @param $funcName
     * @param $userId
     * @return array
     */
    public static function routeLock($funcName , $userId = null)
    {
        //并发锁
        $getLockName = 'routeLock:'.request()->route()->uri().'/'.$funcName;

        if(!empty($userId)){
            $getLockName.= ':'.$userId;
        }
        $lock = Cache::lock($getLockName, 60);
        if (!$lock->get()) {
            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'msg' => trans('你点击太快了,慢一点'),
            ];
        }
        dispatch(function () use ($lock) {
            $lock->release();
        })->afterResponse();

        return [
            'code'  => Response::HTTP_OK,
        ];

    }


    //获取今天结束的秒速
    //获取到今天结束剩余多少秒
    public static function getSecondsToEndOfDay()
    {
        $now = now();
        $endOfDay = $now->copy()->endOfDay();
        $secondsToEnd = $now->diffInSeconds($endOfDay);

        return $secondsToEnd;
    }


    /**
     * 根据秒数获取格式化文字
     * @param $start int 秒数
     * @return string
     */
    public static function getExecutionTime($start)
    {
        $end = microtime(true); // 获取结束时间
        $executionTime = $end - $start; // 计算执行时间，单位为秒

        $hours = floor($executionTime / 3600);
        $minutes = floor(($executionTime % 3600) / 60);
        $seconds = $executionTime % 60;

        // 格式化输出文字
        return "执行时间：" . $executionTime . " 秒; " . $hours . "小时" . $minutes . "分钟" . $seconds . "秒";
    }


}
