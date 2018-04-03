<?php
namespace Luclin\Meta;

/**
 * 标准Struct基类
 *
 * 一个原则：读取单个动态属性时不判断default结构中是否存在，但写入时限制。
 *
 * deprecated的策略是当单个存取的时候不受影响，但批量读取、处理、填充时将默认跳过。
 *
 * @author andares
 */
abstract class Struct extends Collection
{
    use StructTrait;
}
