<?php

namespace App\common;

/**
 * VIP等级枚举类
 * 
 * 定义用户的VIP等级，从普通用户到VIP6级
 */
class VipLevel
{
    // 普通用户
    public const NORMAL = 0;
    
    // VIP等级
    public const VIP1 = 1;
    public const VIP2 = 2;
    public const VIP3 = 3;
    public const VIP4 = 4;
    public const VIP5 = 5;
    
    // VIP等级名称映射
    public const LEVEL_NAMES = [
        self::NORMAL => '普通用户',
        self::VIP1 => 'VIP1',
        self::VIP2 => 'VIP2',
        self::VIP3 => 'VIP3',
        self::VIP4 => 'VIP4',
        self::VIP5 => 'VIP5',
    ];
    
    // VIP等级颜色映射（可用于前端显示）
    public const LEVEL_COLORS = [
        self::NORMAL => '#909399', // 灰色
        self::VIP1 => '#409EFF',   // 蓝色
        self::VIP2 => '#67C23A',   // 绿色
        self::VIP3 => '#E6A23C',   // 黄色
        self::VIP4 => '#F56C6C',   // 红色
        self::VIP5 => '#9966FF',   // 紫色
    ];
    
    /**
     * 获取VIP等级名称
     * 
     * @param int $level VIP等级
     * @return string VIP等级名称
     */
    public static function getLevelName(int $level): string
    {
        return self::LEVEL_NAMES[$level] ?? '未知等级';
    }
    
    /**
     * 获取VIP等级颜色
     * 
     * @param int $level VIP等级
     * @return string VIP等级颜色代码
     */
    public static function getLevelColor(int $level): string
    {
        return self::LEVEL_COLORS[$level] ?? '#909399';
    }
    
    /**
     * 检查是否为VIP用户
     * 
     * @param int $level VIP等级
     * @return bool 是否为VIP用户
     */
    public static function isVip(int $level): bool
    {
        return $level >= self::VIP1 && $level <= self::VIP5;
    }
    
    /**
     * 获取所有VIP等级选项（用于表单选择等）
     * 
     * @return array VIP等级选项数组
     */
    public static function getLevelOptions(): array
    {
        $options = [];
        foreach (self::LEVEL_NAMES as $level => $name) {
            $options[] = [
                'value' => $level,
                'label' => $name,
                'color' => self::getLevelColor($level)
            ];
        }
        return $options;
    }
}