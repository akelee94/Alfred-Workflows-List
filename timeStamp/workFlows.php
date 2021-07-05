<?php

/**
 * Name   Workflows
 * Desc   针对官方提供的PHP类库进行优化更进
 *
 * Extra  提供对数据的检索、解析、缓存、自动过期清理
 *
 * Author 我是阿沐(Jonny Lee)
 * Date   2021-07-01
 * Email lw1772363381@163.com
 * Version 1.0
 */
class workFlows
{
    /**
     * @desc 缓存路径
     * @var string
     */
    private $cache = null;

    /**
     * @desc 保存值到指定的plist文件路径
     * @var string
     */
    private $data = null;

    /**
     * @desc 工作流的绑定id的值
     * @var null
     */
    private $bundle = null;

    /**
     * @desc 获取当前文件路径
     * @var string
     */
    private $path = null;

    /**
     * @desc home目录路径
     * @var string|null
     */
    private $home = null;

    /**
     * @desc 结果集
     * @var array|null
     */
    private $results = [];

    /**
     * @desc 构造函数
     * Workflows constructor.
     * @param null $bundle_id 工作流id
     */
    function __construct($bundle_id = null)
    {
        $this->path = exec('pwd');
        $this->home = exec('printf $HOME');

        if (file_exists('info.plist')) $this->bundle = $this->get('bundleid', 'info.plist');

        // 如果存在指定工作流id 则使用
        if (!is_null($bundle_id)) $this->bundle = $bundle_id;

        $this->cache = $this->appointPath('com.runningwithcrayons.Alfred', $this->home . '/Library/Caches/') . "/Workflow Data/" . $this->bundle;

        $this->data = $this->appointPath('Alfred', $this->home . "/Library/Application Support") . "Workflow Data/" . $this->bundle;

        if (!file_exists($this->cache)) {
            exec("mkdir '" . $this->cache . "'");
        }

        if (!file_exists($this->data)) {
            exec("mkdir '" . $this->data . "'");
        }

        $this->results = [];
    }

    /**
     * @desc 自动获取工作流指定目录  随着版本更新自动获取
     * @param string $prefix 文件前缀
     * @param string $path 文件夹路径
     * @return string        返回执行文件夹全路径
     */
    private function appointPath($prefix = '', $path = '')
    {
        if (!$prefix || !$path) return false;

        // 打开文件
        $resource = opendir($path);

        // 符合要求的结果集
        $list = [];

        // 遍历拿到第一个符合要求的数据则退出 返回
        while ($filename = readdir($resource)) {
            // 过滤根目录层级且必须是文件夹
            if ($filename !== '..' || $filename !== '.') {
                if (strpos($prefix, $filename) === 0) {
                    array_push($list, $filename);
                }
            }
        }

        // 获取最后一个文件夹
        $last_folder = '';

        if (count($list) > 0) $last_folder = $list[count($list) - 1];

        return $path . ($last_folder ? $last_folder : $prefix);
    }

    /**
     * @desc 返回工作流绑定的id值
     * @return bool|null
     */
    public function bundle()
    {
        if (is_null($this->bundle)) return false;

        return $this->bundle;
    }

    /**
     * @desc 返回缓存目录的路径值
     * @return bool|string 不存在则返回 false
     */
    public function cache()
    {
        if (is_null($this->bundle) || is_null($this->cache)) return false;

        return $this->cache;
    }

    /**
     * @desc 返回存储目录的路径值
     * @return bool|string
     */
    public function data()
    {
        if (is_null($this->bundle) || is_null($this->data)) return false;

        return $this->data;
    }

    /**
     * @desc 返回当前目录的路径值
     * @return bool|string
     */
    public function path()
    {
        if (is_null($this->path)) return false;

        return $this->path;
    }

    /**
     * @desc 返回当前用户的主路径值
     * @return bool|string|null
     */
    public function home()
    {
        if (is_null($this->home)) return false;

        return $this->home;
    }

    /**
     * @desc 返回结果集列表
     * @return array|null
     */
    public function results()
    {
        return $this->results;
    }

    /**
     * @desc 将关联数组转换为XML格式
     * @param null $data 要转换的关联数组
     * @return bool|mixed     数组的XML字符串表示形式
     */
    public function toXml($data = null)
    {
        // 检测是否是json格式数据  是则转换给数组格式
        if (!is_null(json_decode($data))) $data = json_decode($data, true);

        // 若果传来的数据是为空 则判断 results 是否有结果集 有则赋值给 data
        if (is_null($data)) {

            if (empty($this->results)) return false;

            $data = $this->results;
        }

        // 创建 XML 对象
        $items = new SimpleXMLElement("<items></items>");

        // 遍历组装xml数据
        foreach ($data as $value) {
            // 给 xml 对象增加一个 item 节点
            $item = $items->addChild('item');
            // 获取数组中的 键名
            $item_keys = array_keys($value);
            foreach ($item_keys as $key) {
                if ($key == 'uid') {
                    $item->addAttribute('uid', $value[$key]);
                } elseif ($key == 'arg') {
                    $item->addAttribute('arg', $value[$key]);
                } elseif ($key == 'type') {
                    $item->addAttribute('type', $value[$key]);
                } elseif ($key == 'valid') {
                    if ($value[$key] == 'yes' || $value[$key] == 'no') $item->addAttribute('valid', $value[$key]);
                } elseif ($key == 'autocomplete') {
                    $item->addAttribute('autocomplete', $value[$key]);
                } elseif ($key == 'icon') {
                    if (substr($value[$key], 0, 9) == 'fileicon:') {
                        $val = substr($value[$key], 9);
                        $item->$key = $val;
                        $item->$key->addAttribute('type', 'fileicon');
                    } elseif (substr($value[$key], 0, 9) == 'filetype:') {
                        $val = substr($value[$key], 9);
                        $item->$key = $val;
                        $item->$key->addAttribute('type', 'filetype');
                    } else {
                        $item->$key = $value[$key];
                    }
                } else {
                    $item->$key = $value[$key];
                }
            }
        }
        return $items->asXML();
    }

    /**
     * @desc 判断当前参数是否为空
     * @param string $data
     * @return bool
     */
    private function filterEmptyValue($data = '')
    {
        if (!$data || empty($data)) return false;

        return true;
    }

    /**
     * @desc 设置值保存在plist文件中
     * ① 第一个参数若是数组 则第二个则是保存值的plist文件
     * ② 第一个参数若是字符串 则第二个则是保存的值 第三个是保存值的plist文件
     * @param null $param1
     * @param null $param2
     * @param null $param3
     * @return bool
     */
    public function set($param1 = null, $param2 = null, $param3 = null)
    {
        // 如果是数组格式
        if (is_array($param1)) {
            if (file_exists($param2)) {
                $param2 = $this->path . "/" . $param2;
            } elseif (file_exists($this->data . "/" . $param2)) {
                $param2 = $this->data . "/" . $param2;
            } elseif (file_exists($this->cache . "/" . $param2)) {
                $param2 = $this->cache . "/" . $param2;
            } else {
                $param2 = $this->data . "/" . $param2;
            }
            foreach ($param1 as $key => $value) {
                exec('defaults write "' . $param2 . '" ' . $key . ' "' . $value . '"');
            }

            return true;
        }

        // 如果是字符串格式
        if (file_exists($param3)) {
            $param3 = $this->path . "/" . $param3;
        } elseif (file_exists($this->data . "/" . $param3)) {
            $param3 = $this->data . "/" . $param3;
        } elseif (file_exists($this->cache . "/" . $param3)) {
            $param3 = $this->cache . "/" . $param3;
        } else {
            $param3 = $this->data . "/" . $param3;
        }

        exec('defaults write "' . $param3 . '" ' . $param1 . ' "' . $param2 . '"');

        return true;
    }

    /**
     * @desc 从指定的plist读取值
     * @param string $value 要读取的值
     * @param string $path 要读取值的文件
     * @return bool|mixed
     */
    public function get($value = '', $path = '')
    {
        if (!$value || !$path) return false;

        if (file_exists($path)) {
            $path = $this->path . "/" . $path;
        } elseif (file_exists($this->data . "/" . $path)) {
            $path = $this->data . "/" . $path;
        } elseif (file_exists($this->cache . "/" . $path)) {
            $path = $this->cache . "/" . $path;
        } else {
            return false;
        }

        exec('defaults read "' . $path . '" ' . $value, $out);

        if (!$out) return false;

        return $out[0];
    }

    /**
     * @desc 从远程文件/url读取数据
     * @param string $url 请求的url
     * @param null $options 请求需要的配置项
     * @return bool|string
     */
    public function request($url = '', $options = null)
    {
        if (is_null($url)) return false;

        $defaults = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => true
        ];

        if ($options) {
            foreach ($options as $key => $val) {
                $defaults[$key] = $val;
            }
        }

        // 过滤空数组
        array_filter($defaults, array($this, 'filterEmptyValue'));

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $out = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return $error;

        return $out;
    }

    /**
     * @desc 允许使用 mdfind 搜索本地硬盘
     * @param $query  搜索字符串
     * @return mixed 搜索结果数组
     */
    public function mdFind($query = '')
    {
        if (!$query) return false;

        exec('mdfind "' . $query . '"', $results);

        return $results;
    }

    /**
     * @desc 接受数据和字符串文件名以将数据作为缓存存储到本地文件
     * @param $data null 保存到文件的数据
     * @param string $filename 将缓存数据写入的文件名
     * @return bool
     */
    public function write($data, $filename = '')
    {
        if (!$data || !$filename) return false;

        if (file_exists($filename)) {
            $filename = $this->path . "/" . $filename;
        } elseif (file_exists($this->data . "/" . $filename)) {
            $filename = $this->data . "/" . $filename;
        } elseif (file_exists($this->cache . "/" . $filename)) {
            $filename = $this->cache . "/" . $filename;
        } else {
            $filename = $this->data . "/" . $filename;
        }

        if (is_array($data)) {
            $data = json_encode($data);
            file_put_contents($filename, $data);
            return true;
        }

        if (is_string($data)) {
            file_put_contents($filename, $data);
            return true;
        }

        return false;
    }

    /**
     * @desc 从本地缓存文件返回数据
     * @param string $filename 从中读取缓存数据的文件名
     * @return bool|false|mixed|string
     */
    public function read($filename = '')
    {
        if (!$filename) return false;

        if (file_exists($filename)) {
            $filename = $this->path . "/" . $filename;
        } elseif (file_exists($this->data . "/" . $filename)) {
            $filename = $this->data . "/" . $filename;
        } elseif (file_exists($this->cache . "/" . $filename)) {
            $filename = $this->cache . "/" . $filename;
        } else {
            return false;
        }

        $out = file_get_contents($filename);

        if (!is_null(json_decode($out))) return json_decode($out);

        return $out;
    }

    /**
     * @desc 创建一个数组结果传递回Alfred
     * @param null $uid 结果的uid应该是唯一的
     * @param null $arg 将传递的参数
     * @param string $title 结果项的标题
     * @param string $sub 结果项的副标题文本
     * @param string $icon 用于结果项的图标
     * @param string $valid 设置是否可以操作结果项
     * @param null $auto 结果项的自动完成值
     * @param null $type
     * @return array
     */
    public function result($uid, $arg, $title, $sub, $icon, $valid = 'yes', $auto = null, $type = null)
    {
        $item = [
            'uid' => $uid,
            'arg' => $arg,
            'title' => $title,
            'subtitle' => $sub,
            'icon' => $icon,
            'valid' => $valid,
            'autocomplete' => $auto,
            'type' => $type
        ];

        if (is_null($type)) unset($item['type']);

        array_push($this->results, $item);

        return $item;
    }

}