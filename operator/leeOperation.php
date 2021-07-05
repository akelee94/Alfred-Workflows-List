<?php
/**
 * @desc  获取两数之间商和模
 * Author 我是阿沐(Jonny Lee)
 * Date   2021-07-01
 * Email lw1772363381@163.com
 */
require_once 'workFlows.php';

/**
 * @desc 整合运算快捷方式
 * Class leeOperation
 */
class leeOperation
{
    /**
     * @desc 左边部分运算数据
     * @var int
     */
    protected $left_operand = 0;

    /**
     * @desc 右边部分运算数据
     * @var int
     */
    protected $right_operand = 0;

    /**
     * @desc 副标题展示
     * @var string
     */
    protected $sub_title = '当前进行 %s 运算，结果集显示:%u';

    /**
     * @desc 异常提示信息
     * @var string
     */
    protected $abnormal_title = '警告，你在乱搞哦，请输入正确的参数';

    /**
     * @desc 构造函数
     * Operation constructor.
     */
    public function __construct($query = '')
    {
        $this->workflows = new Workflows();

        $this->resolutionParams($query);
    }

    /**
     * @desc  计算两数的商
     * @return bool
     */
    public function div()
    {
        $div = 0;
        $title = $this->abnormal_title;
        if ($this->right_operand !== 0) {
            $div = intval($this->left_operand / $this->right_operand);
            $title = sprintf($this->sub_title, '商', $div);
        }

        $this->workflows->result(1, $div, $div, $title, 'icon.png');

        return true;
    }

    /**
     * @desc 计算两数的取模结果集
     * @return bool
     */
    public function mod()
    {
        $mod = 0;
        $title = $this->abnormal_title;
        if ($this->right_operand !== 0) {
            $mod = $this->left_operand % $this->right_operand;
            $title = sprintf($this->sub_title, '模', $mod);
        }

        $this->workflows->result(1, $mod, $mod, $title, 'icon.png');

        return true;
    }

    /**
     * @desc 解析运算参数
     * @param string $string
     * @return bool
     */
    private function resolutionParams($string = '')
    {
        $result = is_null($string) ? '' : $string;

        if (empty($result)) return false;

        if (strpos($result, '|') === false) return false;

        list($left, $right) = explode('|', $result);

        if (!$left || !$right) return false;

        $this->left_operand = $left;
        $this->right_operand = $right;
    }

    /**
     * @desc 析构函数
     */
    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        echo $this->workflows->toXml();
    }
}