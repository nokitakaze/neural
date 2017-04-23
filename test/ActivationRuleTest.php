<?php

    require_once 'AbstractNetworkTest.php';
    require_once __DIR__.'/../src/ActivationRule.php';
    require_once __DIR__.'/../src/NetworkException.php';

    class ActivationRuleTest extends AbstractNetworkTest {
        function dataSoft_max() {
            return [
                ['input' => [1, 2, 3, 4, 1, 2, 3,],
                 'output' => [0.024, 0.064, 0.175, 0.475, 0.024, 0.064, 0.175],],
                ['input' => [3, 1, 0.2],
                 'output' => [0.836, 0.113, 0.050836],],
                ['input' => [-6, -8],
                 'output' => [0.8808, 0.1192],],
                ['input' => [1, 2, 3],
                 'output' => [0.09, 0.2447, 0.6652],],
                ['input' => [1, 2, 5],
                 'output' => [0.0171, 0.0466, 0.9362],],
                ['input' => [1, 2, 5],
                 'output' => [0.0171, 0.0466, 0.9362],],
                ['input' => [5.075, 8.137],
                 'output' => [0.0447, 0.9553],],
            ];
        }

        /**
         * @param double[] $input
         * @param double[] $output
         *
         * @dataProvider dataSoft_max
         */
        function testSoft_max($input, $output) {
            NokitaKaze\Neural\ActivationRule::soft_max($input);
            foreach ($input as $num => &$value) {
                $this->assertGreaterThanOrEqual($output[$num] - 0.001, $value);
                $this->assertLessThanOrEqual($output[$num] + 0.001, $value);
            }
        }

        /**
         * @param boolean $ignore_plus_number
         *
         * @return array[]
         * @throws Exception
         */
        function dataCalculateSingle($ignore_plus_number = false) {
            $data = [];
            $data[] = [
                'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_TANH,
                'input' => [1.000, 0.500, 0.900, 4.000, 5.000, 7.000],
                'output' => [0.762, 0.462, 0.716, 0.999, 1.000, 1.000],
            ];
            $data[] = [
                'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SIGMOID,
                'input' => [1.000, 0.500, 0.900, 4.000, 5.000, 7.000],
                'output' => [0.731, 0.622, 0.711, 0.982, 0.993, 0.999],
            ];
            $closure_plus_one = function ($value) {
                return $value + 1;
            };
            $closure_plus_one_entire_field = function (&$values) {
                $values = array_map(function ($value) {
                    return $value + 1;
                }, $values);
            };
            $closure_plus_number = function (&$values) {
                foreach ($values as $num => &$value) {
                    $value += $num;
                }
            };

            $points = [-10, -1, 0, 1, 10];

            foreach ([2, 10, 100] as $length) {//
                foreach ($points as $num => &$point1) {
                    for ($n = $num + 1; $n < count($points); $n++) {
                        $point2 = $points[$n];
                        foreach ([1, 1000] as &$modifier) {
                            $min_value = $point1 * $modifier;
                            $max_value = $point2 * $modifier;

                            for ($i = 0; $i < 10; $i++) {
                                $input = [];
                                $output_tanh = [];
                                $output_sigmoid = [];
                                $output_plus_one = [];
                                $output_plus_number = [];

                                for ($j = 0; $j < $length; $j++) {
                                    $value = mt_rand($min_value, $max_value) / $modifier;
                                    $input[] = $value;
                                    $output_tanh[] = tanh($value);
                                    $output_sigmoid[] = pow(1 + exp(-$value), -1);
                                    $output_plus_one[] = $value + 1;
                                    $output_plus_number[] = $value + $j;
                                }
                                $data[] = [
                                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_TANH,
                                    'input' => $input,
                                    'output' => $output_tanh,
                                ];
                                $data[] = [
                                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SIGMOID,
                                    'input' => $input,
                                    'output' => $output_sigmoid,
                                ];
                                $data[] = [
                                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SELF_METHOD,
                                    'input' => $input,
                                    'output' => $output_plus_one,
                                    'closure' => $closure_plus_one,
                                ];
                                $data[] = [
                                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SELF_METHOD_OVER_FIELD,
                                    'input' => $input,
                                    'output' => $output_plus_one,
                                    'closure' => $closure_plus_one_entire_field,
                                ];
                                if (!$ignore_plus_number) {
                                    $data[] = [
                                        'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SELF_METHOD_OVER_FIELD,
                                        'input' => $input,
                                        'output' => $output_plus_number,
                                        'closure' => $closure_plus_number,
                                    ];
                                }
                                unset($output_tanh, $output_sigmoid, $output_plus_one, $output_plus_number);

                                //
                                $max = $input[0];
                                foreach ($input as &$value) {
                                    $max = max($max, $value);
                                }
                                $scale = 0;
                                foreach ($input as &$value) {
                                    $scale += exp($value - $max);
                                }

                                $output_softmax = array_map(function ($value) use ($max, $scale) {
                                    return exp($value - $max) / $scale;
                                }, $input);

                                $data[] = [
                                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SOFTMAX,
                                    'input' => $input,
                                    'output' => $output_softmax,
                                ];
                                unset($input, $output_softmax, $scale, $max, $value);
                            }
                        }
                    }
                    unset($n, $min_value, $max_value, $modifier);
                }
                unset($num, $point1, $point2, $modifier);
            }
            unset($points);

            // @todo протестировать все нули

            foreach ($this->dataSoft_max() as &$datum) {
                $data[] = [
                    'type' => NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SOFTMAX,
                    'input' => $datum['input'],
                    'output' => $datum['output'],
                ];
            }

            foreach ($data as &$datum) {
                if (count($datum['input']) != count($datum['output'])) {
                    throw new Exception('Wrong data generated');
                }
            }

            $real_data = [];
            foreach (array_chunk($data, 20) as &$datum) {
                $real_data[] = [
                    'full_input' => $datum,
                ];
            }

            return $real_data;
        }

        /**
         * @param array[] $full_input
         *
         * @dataProvider dataCalculateSingle
         * @covers       \NokitaKaze\Neural\ActivationRule::get_activation_method
         * @covers       \NokitaKaze\Neural\ActivationRule::get_type
         */
        function testCalculateSingle($full_input) {
            /**
             * @var \Closure|null $closure
             */
            foreach ($full_input as $data) {
                $closure = isset($data['closure']) ? $data['closure'] : null;

                $rule = new NokitaKaze\Neural\ActivationRule($data['type'], [], $closure);
                $this->assertEquals($data['type'], $rule->get_type());
                if (!is_null($closure)) {
                    $this->assertEquals(spl_object_hash($closure), spl_object_hash($rule->get_activation_method()));
                } else {
                    $this->assertNull($rule->get_activation_method());
                }

                $clone = $data['input'];
                $rule->calculate($clone);
                foreach ($clone as $num => &$value) {
                    $this->assertGreaterThanOrEqual($data['output'][$num] - 0.001, $value);
                    $this->assertLessThanOrEqual($data['output'][$num] + 0.001, $value);
                }
            }
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @covers \NokitaKaze\Neural\ActivationRule::__construct
         * @covers \NokitaKaze\Neural\ActivationRule::calculate
         */
        function testCalculateException() {
            $rule = new NokitaKaze\Neural\ActivationRule(mt_rand(15, 100));
            $input = range(1, 3);
            $rule->calculate($input);
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @covers \NokitaKaze\Neural\ActivationRule::__construct
         * @covers \NokitaKaze\Neural\ActivationRule::calculate_interval
         * @covers \NokitaKaze\Neural\ActivationRule::get_activation_method
         * @covers \NokitaKaze\Neural\ActivationRule::get_type
         */
        function testCalculateExceptionInIntervals() {
            $interval = (object) ['in' => [0, 1, 2], 'type' => mt_rand(15, 100),];
            $rule = new NokitaKaze\Neural\ActivationRule(\NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_INTERVAL,
                [$interval,]);
            $this->assertNull($rule->get_activation_method());
            $this->assertEquals(\NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_INTERVAL, $rule->get_type());
            //$this->assertEquals([$interval,], $rule->get_intervals());
            $input = range(1, 3);
            $rule->calculate($input);
        }

        function dataCalculateInterval() {
            $raw_data = $this->dataCalculateSingle(true);
            $plain_data = [];
            foreach ($raw_data as &$datum) {
                $plain_data = array_merge($plain_data, $datum['full_input']);
            }
            unset($raw_data, $datum);

            $data = [];
            $data[] = [
                'intervals' => [
                    (object) [
                        'in' => [0, 1, 4],
                        'type' => \NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SOFTMAX,
                    ],
                    (object) [
                        'in' => [2, 3],
                        'type' => \NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SOFTMAX,
                    ],
                ],
                'input' => [1, 2, -6, -8, 5],
                'output' => [0.0171, 0.0466, 0.8808, 0.1192, 0.9362],
            ];

            //
            for ($i = 0; $i < 200; $i++) {
                $len = mt_rand(2, 4);

                $datum_input = [];
                $datum_output = [];
                /**
                 * @var NokitaKaze\Neural\ActivationRuleInterval[] $intervals
                 */
                $intervals = [];
                for ($j = 0; $j < $len; $j++) {
                    $type = mt_rand(0, 1);
                    if ($type == 0) {
                        $r = $plain_data[mt_rand(0, count($plain_data) - 1)];
                        $intervals[] = (object) [
                            'min' => count($datum_input),
                            'max' => count($datum_input) + count($r['input']) - 1,
                            'type' => $r['type'],
                            'method' => isset($r['closure']) ? $r['closure'] : null,
                        ];
                        $datum_input = array_merge($datum_input, $r['input']);
                        $datum_output = array_merge($datum_output, $r['output']);
                        unset($r);
                    } elseif ($type == 1) {
                        $sub_length = mt_rand(2, 4);

                        /**
                         * @var double[] $sub_datum_input
                         * @var double[] $sub_datum_output
                         */
                        $sub_datum_input = [];
                        $sub_datum_output = [];
                        $numbers = [];
                        $rules = [];
                        for ($n = 0; $n < $sub_length; $n++) {
                            $r = $plain_data[mt_rand(0, count($plain_data) - 1)];
                            $rules[] = $r;
                            $sub_datum_input = array_merge($sub_datum_input, $r['input']);
                            $sub_datum_output = array_merge($sub_datum_output, $r['output']);
                            $numbers = array_merge($numbers, array_fill(0, count($r['input']), $n));
                        }
                        $indexes = range(0, count($numbers) - 1);

                        //
                        $numbers_new = [];
                        $sub_datum_input_new = [];
                        $sub_datum_output_new = [];
                        shuffle($indexes);
                        foreach ($indexes as $new_index => $old_index) {
                            $numbers_new[] = $numbers[$old_index];
                            $sub_datum_input_new[] = $sub_datum_input[$old_index];
                            $sub_datum_output_new[] = $sub_datum_output[$old_index];
                        }
                        unset($sub_datum_input, $sub_datum_output, $numbers);
                        for ($n = 0; $n < $sub_length; $n++) {
                            $ids = [];
                            foreach ($numbers_new as $index => $rule_id) {
                                if ($rule_id == $n) {
                                    $ids[] = $index + count($datum_input);
                                }
                            }

                            $intervals[] = (object) [
                                'in' => $ids,
                                'type' => $rules[$n]['type'],
                                'method' => isset($rules[$n]['closure']) ? $rules[$n]['closure'] : null,
                            ];
                            unset($ids, $rule_id);
                        }
                        $datum_input = array_merge($datum_input, $sub_datum_input_new);
                        $datum_output = array_merge($datum_output, $sub_datum_output_new);
                        unset($rules, $r, $n, $numbers_new, $sub_datum_input_new, $sub_datum_output_new);
                    }
                    unset($type, $numbers, $n);
                }

                $used_ids = [];
                foreach ($intervals as $interval) {
                    if (isset($interval->in)) {
                        $new_ids = $interval->in;
                    } else {
                        $new_ids = range($interval->min, $interval->max);
                    }
                    if (count(array_intersect($used_ids, $new_ids)) > 0) {
                        throw new Exception('Interval error');
                    }
                    $used_ids = array_merge($used_ids, $new_ids);
                }
                if ((count($datum_input) != count($used_ids)) or (count($datum_output) != count($used_ids))) {
                    throw new Exception('Interval error: count');
                }
                unset($used_ids, $new_ids, $interval);

                $data[] = [
                    'intervals' => $intervals,
                    'input' => $datum_input,
                    'output' => $datum_output,
                ];
                unset($j, $type, $len, $intervals, $datum_input, $datum_output);
            }

            return $data;
        }

        /**
         * @param \NokitaKaze\Neural\ActivationRuleInterval[] $intervals
         * @param double[]                                    $input
         * @param double[]                                    $output
         *
         * @dataProvider dataCalculateInterval
         *
         * @covers       \NokitaKaze\Neural\ActivationRule::__construct
         * @covers       \NokitaKaze\Neural\ActivationRule::calculate
         * @covers       \NokitaKaze\Neural\ActivationRule::calculate_intervals
         * @covers       \NokitaKaze\Neural\ActivationRule::calculate_interval
         * @covers       \NokitaKaze\Neural\ActivationRule::get_intervals
         */
        function testCalculateInterval($intervals, $input, $output) {
            $rule = new NokitaKaze\Neural\ActivationRule(\NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_INTERVAL,
                $intervals);
            $this->assertEquals(\NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_INTERVAL, $rule->get_type());
            foreach ($rule->get_intervals() as $num => $actual_interval) {
                $this->assertIntervalEquals($intervals[$num], $actual_interval);
            }
            $this->assertNull($rule->get_activation_method());
            $rule->calculate($input);
            foreach ($input as $num => &$value) {
                $this->assertGreaterThanOrEqual($output[$num] - 0.001, $value);
                $this->assertLessThanOrEqual($output[$num] + 0.001, $value);
            }
        }

        /**
         * @param \NokitaKaze\Neural\ActivationRuleInterval $interval1
         * @param \NokitaKaze\Neural\ActivationRuleInterval $interval2
         */
        function assertIntervalEquals($interval1, $interval2) {
            if (isset($interval1->in)) {
                $i1 = $interval1->in;
                sort($i1);
                $i2 = $interval2->in;
                sort($i2);
                $this->assertEquals($i1, $i2);
            } else {
                $this->assertFalse(isset($interval2->in));
                $this->assertEquals($interval1->min, $interval2->min);
                $this->assertEquals($interval1->max, $interval2->max);
            }

            $this->assertEquals($interval1->type, $interval2->type);
            if (isset($interval1->method)) {
                $this->assertEquals(is_null($interval1->method), is_null($interval2->method));
                if (!is_null($interval1->method)) {
                    $this->assertEquals(spl_object_hash($interval1->method), spl_object_hash($interval2->method));
                }
            } else {
                $this->assertFalse(isset($interval2->method));
            }
        }
    }

?>