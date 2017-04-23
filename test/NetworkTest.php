<?php

    require_once 'AbstractNetworkTest.php';
    require_once __DIR__.'/../src/NetworkException.php';
    require_once __DIR__.'/../src/ActivationRule.php';
    require_once __DIR__.'/../src/Network.php';

    class NetworkTest extends AbstractNetworkTest {
        protected $_no_read_file = null;

        function tearDown() {
            if (!is_null($this->_no_read_file) and file_exists($this->_no_read_file)) {
                @unlink($this->_no_read_file);
            }

            parent::tearDown();
        }

        function dataGet_weights_number() {
            $data = [];
            $data[] = [
                'geometry' => [3, 4, 3],
                'count' => 12 + 12 + 4 + 3,
            ];

            return $data;
        }

        /**
         * @var integer[] $geometry
         * @var integer   $count
         *
         * @dataProvider dataGet_weights_number
         */
        function testGet_weights_number($geometry, $count) {
            $network = new NokitaKaze\Neural\Network();

            $reflection = new ReflectionProperty($network, '_geometry');
            $reflection->setAccessible(true);
            $reflection->setValue($network, $geometry);

            $this->assertEquals($count, $network->get_weights_number());
        }

        function dataBin($method) {
            $data = [];
            $data[] = [
                'filename' => 'test1.dat',
                'weights' => [
                    0.6973, 0.7116, 0.3202, -0.4630, 0.7680, -0.0759, 0.9944, 0.2342, -0.0587, -0.0972,
                    0.6226, 0.5134, 0.4441, 0.9587, 0.0209, 0.6005, -0.1437, 0.0186, 0.4248, 0.1352,
                    -0.3200, 0.8136, -3.0970, -0.3118, 0.3053, -1.3209, 2.3075, -0.0545, 1.1354, 0.7653,
                    -0.1609, -6.5114, 0.3460, -0.8386, -8.3512, 1.2967, -0.6231, -0.8283, -5.1941, 1.7533,
                    3.8349, -1.2867, -0.6193,
                ],
                'tests' => [
                    [
                        'input' => [-1.4232, -8.2036, -3.5440, -8.2284],
                        'output' => [0.0000, 1.0000, 0.0000],
                    ], [
                        'input' => [7.8095, -1.9125, -6.4564, -9.7773],
                        'output' => [0.0000, 0.9997, 0.0003],
                    ], [
                        'input' => [0.0852, -0.4567, -1.5563, 2.7021],
                        'output' => [0.9996, 0.0004, 0.0000],
                    ],
                ],
                'geometry' => [4, 5, 3],
            ];

            $data[] = [
                'filename' => 'test2.dat',
                'weights' => [
                    0.2657, -0.1867, -0.6855, 1.4898, 0.5047, -0.7164, 0.2970, 0.8715, 0.3189, 1.1110,
                    -0.1621, 1.6893, 0.6495, 0.4312, 0.4585, 0.7039, -0.0315, 1.3660, -0.6461, -0.1561,
                    1.8549, -0.9723, 0.3797, -0.1413, 1.2858, 8.2105, -1.7287, -4.2654, -6.8683, -2.5317,
                    8.7693, 3.5879, -10.3762, 0.7316, 1.6264, -3.0934, 3.3044, 2.3086, -6.1332, -15.2261,
                    3.9779, -1.6707, -0.0246,
                ],
                'tests' => [
                    [
                        'input' => [-5.0532, 7.7423, 1.6003, -2.6218],
                        'output' => [0.9893, 0.0000, 0.0107],
                    ], [
                        'input' => [-7.3670, -3.7504, -6.9942, 9.3576],
                        'output' => [0.1313, 0.0000, 0.8687],
                    ], [
                        'input' => [3.1958, 8.4460, 1.6670, -9.2258],
                        'output' => [0.9175, 0.0825, 0.0000],
                    ],
                ],
                'geometry' => [4, 5, 3],
            ];

            $data[] = [
                'filename' => 'test3.dat',
                'weights' => [
                    0.5708, -0.2873, 0.7994, 0.1626, -0.0851, 0.8326, -0.8141, 0.2699, -0.5294, 1.1284,
                    -0.2533, 0.4263, 0.0785, 0.8339, -1.4478, -0.0004, 0.7528, 0.6880, 0.4525, -0.2917,
                    -0.9461, 0.4056, 0.5156, -0.4403, -0.9256, 4.0924, -9.3536, 5.2185, 4.4862, -2.4164,
                    7.5807, -2.0314, -8.3220, -6.3087, -7.4544, -5.6809, -7.7048, -2.9391, -3.2468, 4.1146,
                    -5.0142, -0.4595, -11.9220,
                ],
                'tests' => [
                    [
                        'input' => [3.7801, -4.8261, -1.3940, 2.6279],
                        'output' => [0.0000, 1.0000, 0.0000],
                    ], [
                        'input' => [-8.5709, -3.4547, 6.9282, 1.3540],
                        'output' => [0.0000, 1.0000, 0.0000,],
                    ], [
                        'input' => [-0.8729, -2.7252, 3.8844, 2.5530,],
                        'output' => [0.0000, 1.0000, 0.0000,],
                    ],
                ],
                'geometry' => [4, 5, 3],
            ];

            if (in_array($method, ['testBin'])) {
                $new_data = [];
                foreach ($data as $datum) {
                    foreach (['', 'load_network', 'direct'] as $method) {
                        $datum['method'] = $method;
                        $new_data[] = $datum;
                    }
                }
                $data = $new_data;
            } elseif (in_array($method, ['testSave'])) {
                $new_data = [];
                foreach ($data as $datum) {
                    foreach (['save_network_to_json', 'save_network_to_bin'] as $method) {
                        $datum['method'] = $method;
                        $datum['type'] = false;
                        $new_data[] = $datum;
                    }
                    foreach ([false, 'json', 'bin'] as $type) {
                        $datum['method'] = 'save_network';
                        $datum['type'] = $type;
                        $new_data[] = $datum;
                    }
                }
                $data = $new_data;
            }

            return $data;
        }

        /**
         * @param string     $filename
         * @param integer[]  $weights
         * @param double[][] $tests
         * @param integer[]  $geometry
         * @param string     $method
         *
         * @dataProvider dataBin
         * @throws Exception
         */
        function testBin($filename, array $weights, array $tests, array $geometry, $method) {
            $full_filename = __DIR__.'/bin/'.$filename;
            if ($method == '') {
                $network = new NokitaKaze\Neural\Network($full_filename);
            } elseif ($method == 'load_network') {
                $network = new NokitaKaze\Neural\Network();
                $network->load_network($full_filename);
            } else {
                $network = new NokitaKaze\Neural\Network();
                if (substr($filename, -4) == 'json') {
                    $network->load_network_from_json($full_filename);
                } elseif (substr($filename, -3) == 'dat') {
                    $network->load_network_from_bin($full_filename);
                } else {
                    throw new Exception('Неправильный extension');
                }
            }

            $actual = $network->get_weights();
            $this->assertEquals($geometry, $network->get_geometry());
            $this->assertEquals(count($weights), count($actual));
            foreach ($weights as $num => &$expected) {
                $this->assertGreaterThanOrEqual($expected - 0.001, $actual[$num]);
                $this->assertLessThanOrEqual($expected + 0.001, $actual[$num]);
            }

            foreach ($tests as &$test) {
                $actual = $network->calculate($test['input']);
                $this->assertEquals(3, count($actual));
                foreach ($test['output'] as $num => &$expected) {
                    $this->assertGreaterThanOrEqual($expected - 0.001, $actual[$num]);
                    $this->assertLessThanOrEqual($expected + 0.001, $actual[$num]);
                }

                $layers = $network->get_layers_after_calculate();
                $this->assertEquals($network->get_layers_count(), count($layers));
                $this->assertEquals($test['input'], $layers[0]);
                $this->assertEquals($actual, $layers[count($layers) - 1]);
            }
        }

        /**
         * @covers NokitaKaze\Neural\Network::get_geometry
         * @covers NokitaKaze\Neural\Network::set_geometry
         * @covers NokitaKaze\Neural\Network::get_activation_rules
         * @covers NokitaKaze\Neural\Network::set_activation_rules
         */
        function testGet_geometry() {
            foreach ([false, true] as $u) {
                if (!$u) {
                    $network = new NokitaKaze\Neural\Network();
                }
                for ($i = 0; $i < 10; $i++) {
                    if ($u) {
                        $network = new NokitaKaze\Neural\Network();
                    }

                    $range = array_fill(0, mt_rand(2, 10), 0);
                    foreach ($range as &$value) {
                        $value = mt_rand(2, 100);
                    }
                    /**
                     * @var NokitaKaze\Neural\Network $network
                     */
                    $network->set_geometry($range);
                    $this->assertEquals($range, $network->get_geometry());

                    $rules = array_fill(0, count($range), null);
                    $hashes = [];
                    foreach ($rules as &$rule) {
                        $type = mt_rand(0, 3);
                        $rule = new \NokitaKaze\Neural\ActivationRule($type);
                        $hashes[] = spl_object_hash($rule);
                    }
                    $network->set_activation_rules($rules);
                    unset($rule, $type);

                    $new_rules = $network->get_activation_rules();
                    foreach ($new_rules as $num => &$rule) {
                        $this->assertEquals(spl_object_hash($rule), $hashes[$num]);
                    }
                }
            }
        }

        /**
         * @param string $filename
         *
         * @dataProvider          dataBin
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 3
         * @covers                \NokitaKaze\Neural\Network::set_weights
         */
        function testSet_weightsException($filename) {
            $full_filename = __DIR__.'/bin/'.$filename;
            $network = new NokitaKaze\Neural\Network($full_filename);
            $new = array_fill(0,
                $network->get_weights_number() + (mt_rand(0, 1) ? mt_rand(1, 10) : mt_rand(-10, -1)), 0);
            $network->set_weights($new);
        }

        function dataConstructException() {
            $this->_no_read_file = tempnam(sys_get_temp_dir(), 'delme_');
            file_put_contents($this->_no_read_file, range(0, 100), LOCK_EX);
            chmod($this->_no_read_file, 0);

            $data = [];
            foreach ([[
                          'filename' => '/dev/null/foobar',
                          'code' => 5,
                      ], [
                          'filename' => $this->_no_read_file,
                          'code' => 6,
                      ]] as $datum) {
                foreach (['', 'load_network', 'load_network_from_json', 'load_network_from_bin'] as $method) {
                    $datum['method'] = $method;
                    $data[] = $datum;
                }
            }

            return $data;
        }

        /**
         * @param string  $filename
         * @param integer $code
         * @param string  $method
         *
         * @throws NokitaKaze\Neural\NetworkException
         * @dataProvider dataConstructException
         */
        function testConstructException($filename, $code, $method) {
            if (!empty($method)) {
                $network = new \NokitaKaze\Neural\Network();
            }
            try {
                if (empty($method)) {
                    new \NokitaKaze\Neural\Network($filename);
                } else {
                    /**
                     * @var \NokitaKaze\Neural\Network $network
                     */
                    $network->$method($filename);
                }
            } catch (\NokitaKaze\Neural\NetworkException $e) {
                $this->assertEquals($code, $e->getCode());

                return;
            }
            $this->fail('Network did not throw NetworkException on method '.$method);
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 4
         */
        function testSet_activation_rulesException() {
            $network = new NokitaKaze\Neural\Network();
            $network->set_geometry([mt_rand(2, 5), mt_rand(2, 5), mt_rand(2, 5)]);

            $rules = array_fill(0, mt_rand(4, 10), null);
            foreach ($rules as &$rule) {
                $rule = new \NokitaKaze\Neural\ActivationRule(mt_rand(0, 3));
            }
            $network->set_activation_rules($rules);
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 4
         */
        function testSet_activation_rulesExceptionBelow() {
            $network = new NokitaKaze\Neural\Network();
            $network->set_geometry([mt_rand(2, 5), mt_rand(2, 5), mt_rand(2, 5)]);

            $rules = array_fill(0, mt_rand(1, 2), null);
            foreach ($rules as &$rule) {
                $rule = new \NokitaKaze\Neural\ActivationRule(mt_rand(0, 3));
            }
            $network->set_activation_rules($rules);
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 2
         */
        function testCalculateException() {
            $network = new NokitaKaze\Neural\Network();
            $network->set_geometry([mt_rand(2, 5), mt_rand(2, 5), mt_rand(2, 5)]);

            $network->set_weights(range(1, $network->get_weights_number()));
            $network->calculate(range(1,
                $network->get_weights_number() + (mt_rand(0, 1) ? 1 : -1) * mt_rand(5, 10)));
        }

        /**
         * @param string       $filename
         * @param integer[]    $weights
         * @param double[][]   $tests
         * @param integer[]    $geometry
         * @param string       $method
         * @param false|string $type
         *
         * @dataProvider dataBin
         * @throws Exception
         */
        function testSave($filename, array $weights, array $tests, array $geometry, $method, $type) {
            $full_filename = __DIR__.'/bin/'.$filename;
            $network = new NokitaKaze\Neural\Network($full_filename);

            foreach (['bin', 'json'] as $ext) {
                $this->_no_read_file = tempnam(sys_get_temp_dir(), 'delme_knt_neural_').'.'.$ext;

                if ($method == 'save_network') {
                    $network->save_network($this->_no_read_file, $type);
                } else {
                    $reflection = new ReflectionMethod($network, $method);
                    $reflection->setAccessible(true);
                    $reflection->invoke($network, $this->_no_read_file);
                }

                $this->assertFileExists($this->_no_read_file);
                $buf = file_get_contents($this->_no_read_file);

                if (($type == 'json') or ($method == 'save_network_to_json')) {
                    $this->assertNotNull(json_decode($buf));
                } elseif (($type == 'bin') or ($method == 'save_network_to_bin')) {
                    $this->assertEquals('NKNN', substr($buf, 0, 4));
                } elseif ($ext == 'json') {
                    $this->assertNotNull(json_decode($buf));
                } else {
                    $this->assertEquals('NKNN', substr($buf, 0, 4));
                }

                $network2 = new \NokitaKaze\Neural\Network($this->_no_read_file);
                $this->assertNetworkEquals($network, $network2);
            }
        }

        /**
         * @param \NokitaKaze\Neural\Network $network1
         * @param \NokitaKaze\Neural\Network $network2
         */
        function assertNetworkEquals($network1, $network2) {
            if (spl_object_hash($network1) == spl_object_hash($network2)) {
                return;
            }

            $this->assertEquals($network1->get_geometry(), $network2->get_geometry());
            $this->assertEquals($network1->get_weights(), $network2->get_weights());

            $rules1 = $network1->get_activation_rules();
            $rules2 = $network2->get_activation_rules();
            $this->assertEquals(count($rules1), count($rules2));
            foreach ($rules1 as $num => &$rule1) {
                $rule2 = $rules2[$num];
                if ($rule1 === null) {
                    $this->assertNull($rule2);
                    continue;
                } else {
                    $this->assertNotNull($rule2);
                }
                if (spl_object_hash($rule1) == spl_object_hash($rule2)) {
                    continue;
                }
                $this->assertEquals($rule1->get_type(), $rule2->get_type());
                if (in_array($rule1->get_type(), [
                    \NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_TANH,
                    \NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SIGMOID,
                    \NokitaKaze\Neural\ActivationRule::TYPE_SINGLE_SOFTMAX,
                ])) {
                    continue;
                }
                // @todo Дописать
                $this->markTestIncomplete('Type incomplete');
            }
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 8
         */
        function testSaveException() {
            $this->_no_read_file = tempnam(sys_get_temp_dir(), 'delme_knt_neural_');
            $network = new NokitaKaze\Neural\Network();
            $network->save_network($this->_no_read_file, 'dat');
        }

        /**
         * @expectedException \NokitaKaze\Neural\NetworkException
         * @expectedExceptionCode 9
         * @covers                \NokitaKaze\Neural\Network::load_network_from_bin_buffer
         */
        function testLoad_network_from_bin_buffer() {
            $this->_no_read_file = tempnam(sys_get_temp_dir(), 'delme_knt_neural_');
            file_put_contents($this->_no_read_file, '1234cfd');
            new NokitaKaze\Neural\Network($this->_no_read_file);
        }
    }

?>