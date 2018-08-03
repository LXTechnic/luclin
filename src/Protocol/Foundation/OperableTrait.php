<?php

namespace Luclin\Protocol\Foundation;

use Luclin\Luri;
use Luclin\MetaInterface;
use Luclin\Operators;
use Luclin\Meta\Collection;
use Luclin\Protocol\Operator;
use Luclin\Support\Recursive;


trait OperableTrait
{
    protected $_operators = [];

    protected $_operatorOutput = [
        'uri'       => false,
        'body'      => true,
        'operator'  => false,
    ];

    public function setOperatorOutputMode(...$modes): self {
        foreach ($this->_operatorOutput as $mode => $state) {
            $this->_operatorOutput[$mode] = false;
        }
        foreach ($modes as $mode) {
            isset($this->_operatorOutput[$mode])
                && $this->_operatorOutput[$mode] = true;
        }
        return $this;
    }

    /**
     * 根据某个操作符名获取操作符列表。
     *
     * 当该操作符名为注册操作符时，会采用操作符对象进行生成。
     * 否则则作为标准的Luri字串进行解析。
     *
     * @param string $name
     * @param array $arguments
     * @return array|null
     */
    public function getOperators(string $name, ...$arguments): ?array {
        if (!isset($this->_operators[$name])) {
            $value = $this->get('$'.$name);
            if ($value === null) {
                throw new \RuntimeException("The operator [$name] not found.");
            }
            if (is_array($value)) {
                $operators = [];
                if (Operator::isRegistered($name)) {
                    foreach ($value as $row) {
                        $operators[] = Operator::make($name, $row, ...$arguments);
                    }
                } else {
                    foreach ($value as $row) {
                        $operators[] = \luc\uri(urldecode($row), ...$arguments);
                    }
                }
                $this->setOperators($name, $operators);
            } else {
                if (Operator::isRegistered($name)) {
                    $operator = Operator::make($name, $value, ...$arguments);
                } else {
                    $operator = \luc\uri(urldecode($value), ...$arguments);
                }
                $this->setOperators($name, [$operator]);
            }
        }

        return $this->_operators[$name];
    }

    /**
     * 直接设置某个操作符的列表。
     *
     * @param string $name
     * @param array $operators
     * @return self
     */
    public function setOperators(string $name, array $operators): self {
        $this->_operators[$name] = $operators;
        return $this;
    }

    /**
     * 对某个命名的操作符进行添加。
     *
     * 这里支持三种类型的operator，其中Operator对象类型的操作符将不能参与输出（因为无法反解析）。
     * 操作符为array时，需要给出[scheme, path, query]各值。
     * 操作符为string时，为完整Luri字串格式。
     * 以上规则对setOperators()方法同样有效。
     *
     * @param string $name
     * @param Operator|string|array $operator
     * @return self
     */
    public function addOperator(string $name, $operator): self {
        $this->_operators[$name][] = $operator;
        return $this;
    }

    public function toArray(callable $filter = null): array {
        $result = parent::toArray($filter);
        return $this->appendOperators2Array($result);
    }

    protected function appendOperators2Array(array $arr): array {
        foreach ($this->_operators as $name => $operators) {
            $key = '$'.$name;
            if (isset($arr[$key])) {
                continue;
            }
            foreach ($operators as $operator) {
                if (is_array($operator)) {
                    $struct = $this->makeOperatorStructByArray($operator);
                    $struct && $arr[$key][] = $struct;
                } elseif (is_string($operator)) {
                    $struct = $this->makeOperatorStructByString($operator);
                    $struct && $arr[$key][] = $struct;
                }
            }
        }
        return $arr;
    }

    protected function makeOperatorStructByArray(array $operator): ?array {
        $luri   = new Luri(...$operator);
        $result = [];
        $this->_operatorOutput['uri']   && $result['uri']   = $luri->render();
        $this->_operatorOutput['body']  && $result['body']  = $luri->toArray();
        $this->_operatorOutput['operator']
            && $result['operator'] = $luri->toOperator();
        return $result;
    }

    protected function makeOperatorStructByString(string $operator): array {
        $luri = Luri::createByUri($operator);
        $result = [];
        $this->_operatorOutput['uri']   && $result['uri']   = $luri->render();
        $this->_operatorOutput['body']  && $result['body']  = $luri->toArray();
        $this->_operatorOutput['operator']
            && $result['operator'] = $luri->toOperator();
        return $result;
    }

}