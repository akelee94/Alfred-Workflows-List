<?php
/**
 * @desc 日期/时间戳/毫秒时间戳 互相转换类
 * Author 我是阿沐(Jonny Lee)
 * Date   2021-07-01
 * Email lw1772363381@163.com
 */
require_once 'workFlows.php';

/**
 * @desc 时间戳格式 和 日期格式互相转换
 * Class DateTimeChange
 */
class dateTimeChange
{
    /**
     * @desc Alfred 传过来的数据
     * @var string
     */
    public $query = '';

    /**
     * @desc 时间戳相关主标题
     * @var string
     */
    private $time_title = '时间戳转换为日期格式';

    /**
     * @desc 时间戳相关副标题
     * @var string
     */
    private $date_title = '日期格式转换为时间戳';

    /**
     * @desc 时间戳相关主标题
     * @var string
     */
    private $mill_time_title = '毫秒时间戳转换为日期格式';

    /**
     * @desc 时间戳相关副标题
     * @var string
     */
    private $mill_date_title = '日期格式转换为毫秒时间戳';

    /**
     * @desc 构造函数
     * dateTimeChange constructor.
     */
    public function __construct($query = '')
    {
//        ini_set('date.timezone', 'Asia/Shanghai'); //设置时区 这两种都可以
        date_default_timezone_set('PRC');
        $this->workflows = new Workflows();
        $this->query = is_null($query) ? '' : $query;
    }

    /**
     * @desc 日期 + 时间戳格式转换
     * @return bool
     */
    public function changeTime()
    {
        $res = str_replace([' ', '\\'], '', $this->query);
        if (empty($res)) {
            $res = $res ? $res : time();
            $date = $sub_title = date('Y-m-d H:i:s', $res);
            $this->workflows->result(1, $date, $date, '默认当前日期格式显示', 'icon.png');
            $this->workflows->result(2, $res, $res, '默认当前时间戳显示', 'icon.png');
        } else {
            // 如果是数字 则说明传过来的是时间戳
            if (is_numeric($this->query)) {
                $sub_title = date('Y-m-d H:i:s', $this->query);
                $this->workflows->result(1, $sub_title, $sub_title, $this->time_title, 'icon.png');
            } else {
                $this->query = str_replace("\\", '', $this->query);
                $sub_title = strtotime($this->query);
                if ($sub_title === false || $sub_title == -1) return false;
                $this->workflows->result(1, $sub_title, $sub_title, $this->date_title, 'icon.png');
            }
        }

        echo $this->workflows->toXml();
    }

    /**
     * @desc 毫秒时间戳+日期转换
     * @param string $query
     */
    public function changeMill($query)
    {
        $res = str_replace([' ', '\\'], '', $query);
        if (empty($res)) {
            $time = $this->getMillTime();
            $date = $this->millToDate($time);
            $this->workflows->result(1, $time, $time, '默认当前毫秒时间戳显示', 'icon.png');
            $this->workflows->result(2, $date, $date, '默认当前毫秒日期格式显示', 'icon.png');
        } else {
            if (is_numeric($query)) {
                $sub_title = $this->millToDate($query);
                $this->workflows->result(1, $sub_title, $sub_title, $this->mill_time_title, 'icon.png');
            } else {
                $sub_title = $this->dateToMill($query);
                $this->workflows->result(1, $sub_title, $sub_title, $this->mill_date_title, 'icon.png');
            }
        }

        echo $this->workflows->toXml();
    }


    /**
     * @desc 获取毫秒时间戳
     * @return float
     */
    protected function getMillTime()
    {
        list($msec, $sec) = explode(' ', microtime());

        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

        return $msectime;
    }

    /**
     * @desc 日期格式转换毫秒时间戳
     * @return string
     */
    protected function dateToMill($query)
    {

        if (strpos($query, '.') === false) return false;

        // 获取 具体日期 + 小数尾部数据
        list($usec, $sec) = explode(".", $query);

        // 替换斜杠
        $usec = str_replace("\\", '', $usec);

        // 日期转换为时间戳
        $date = strtotime($usec);

        // 将小数尾部拼接到时间戳后面 不足13位 向右填充0
        $result = str_pad($date . $sec, 13, "0", STR_PAD_RIGHT);

        return $result;
    }

    /**
     * @desc 毫秒时间戳格式转日期格式
     * @return mixed
     */
    protected function millToDate($query)
    {
        // 先除以1000
        $query = $query * 0.001;

        // 查询小数点所在的位置 并返回小数部分
        if (strstr($query, '.')) {

            // 小数点后不足三位补零 小数点前不足一位补零
            sprintf("%01.3f", $query);

            list($usec, $sec) = explode(".", $query);

            // 向右填充字符串 长度为3
            $sec = str_pad($sec, 3, "0", STR_PAD_RIGHT);
        } else {
            $usec = $query;
            $sec = "000";
        }

        $date = date("Y-m-d H:i:s", $usec) . '.%d';

        return sprintf($date, $sec);
    }
}