<?php

    namespace NokitaKaze\Neural;

    /**
     * Class ActivationRule
     * @package NokitaKaze\Neural
     *
     * @doc https://en.wikipedia.org/wiki/Activation_function
     */
    class ActivationRule {
        /**
         * Hyperbolic tangent
         * @doc https://en.wikipedia.org/wiki/Hyperbolic_function
         */
        const TYPE_SINGLE_TANH = 0;
        /**
         * https://en.wikipedia.org/wiki/Sigmoid_function
         */
        const TYPE_SINGLE_SIGMOID = 1;
        const TYPE_SINGLE_SELF_METHOD = 2;
        const TYPE_SINGLE_SELF_METHOD_OVER_FIELD = 3;
        /**
         * @doc https://en.wikipedia.org/wiki/Softmax_function
         */
        const TYPE_SINGLE_SOFTMAX = 4;
        const TYPE_SINGLE_INTERVAL = 5;

        /**
         * @var integer
         */
        protected $_type;

        /**
         * @var \Closure|null $_activation_method
         */
        protected $_activation_method = null;

        /**
         * @var ActivationRuleInterval[]
         */
        protected $_intervals = [];

        /**
         * ActivationRule constructor.
         *
         * @param integer                  $type
         * @param ActivationRuleInterval[] $intervals
         * @param \Closure|null            $method
         */
        function __construct($type, array $intervals = [], $method = null) {
            $this->_type = $type;
            $this->_intervals = $intervals;
            $this->_activation_method = $method;
        }

        /**
         * @param double[] $values
         *
         * @throws NetworkException
         */
        function calculate(array &$values) {
            switch ($this->_type) {
                case self::TYPE_SINGLE_TANH:
                    $values = array_map(function ($value) {
                        return tanh($value);
                    }, $values);
                    break;
                case self::TYPE_SINGLE_SIGMOID:
                    $values = array_map(function ($value) {
                        return pow(exp(-$value) + 1, -1);
                    }, $values);
                    break;
                case self::TYPE_SINGLE_SELF_METHOD:
                    $values = array_map($this->_activation_method, $values);
                    break;
                case self::TYPE_SINGLE_SELF_METHOD_OVER_FIELD:
                    $closure = $this->_activation_method;
                    $closure($values);
                    break;
                case self::TYPE_SINGLE_SOFTMAX:
                    self::soft_max($values);
                    break;
                case self::TYPE_SINGLE_INTERVAL:
                    $this->calculate_intervals($values);
                    break;
                default:
                    throw new NetworkException('ActivationRule: Unknown type '.$this->_type, 10);
            }
        }

        /**
         * @param double[] $values
         */
        static function soft_max(array &$values) {
            $max = $values[0];
            foreach ($values as &$value) {
                $max = max($max, $value);
            }
            $scale = 0;
            foreach ($values as &$value) {
                $scale += exp($value - $max);
            }

            $scale = pow($scale, -1);
            $values = array_map(function ($value) use ($max, $scale) {
                return exp($value - $max) * $scale;
            }, $values);
        }

        function calculate_intervals(array &$values) {
            foreach ($this->_intervals as &$interval) {
                $projection_values = [];
                if (isset($interval->in)) {
                    $numbers = $interval->in;
                    foreach ($interval->in as &$num) {
                        $projection_values[] = $values[$num];
                    }
                } else {
                    $numbers = range($interval->min, $interval->max);
                    for ($i = $interval->min; $i <= $interval->max; $i++) {
                        $projection_values[] = $values[$i];
                    }
                }

                self::calculate_interval($projection_values, $interval);
                foreach ($numbers as $num => $real_number) {
                    $values[$real_number] = $projection_values[$num];
                }
            }
        }

        /**
         * @param double[]               $values
         * @param ActivationRuleInterval $interval
         *
         * @throws NetworkException
         */
        protected static function calculate_interval(array &$values, $interval) {
            switch ($interval->type) {
                case self::TYPE_SINGLE_TANH:
                    $values = array_map(function ($value) {
                        return tanh($value);
                    }, $values);
                    break;
                case self::TYPE_SINGLE_SIGMOID:
                    $values = array_map(function ($value) {
                        return pow(exp(-$value) + 1, -1);
                    }, $values);
                    break;
                case self::TYPE_SINGLE_SELF_METHOD:
                    $values = array_map($interval->method, $values);
                    break;
                case self::TYPE_SINGLE_SELF_METHOD_OVER_FIELD:
                    $closure = $interval->method;
                    $closure($values);
                    break;
                case self::TYPE_SINGLE_SOFTMAX:
                    self::soft_max($values);
                    break;
                default:
                    throw new NetworkException('ActivationRule: Unknown interval type '.$interval->type, 11);
            }
        }

        /**
         * @return integer
         */
        function get_type() {
            return $this->_type;
        }

        /**
         * @return ActivationRuleInterval[]
         */
        function get_intervals() {
            return $this->_intervals;
        }

        /**
         * @return \Closure|null
         */
        function get_activation_method() {
            return $this->_activation_method;
        }
    }

?>