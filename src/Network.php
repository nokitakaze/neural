<?php

    namespace NokitaKaze\Neural;

    class Network {
        /**
         * @var double[][][]
         */
        protected $_weights;
        /**
         * @var double[][]
         */
        protected $_biases;
        /**
         * @var integer[]
         * Геометрия сети (кол-во нейронов на каждом из слоёв)
         */
        protected $_geometry = [];
        /**
         * @var double[][]
         */
        protected $_values = [];
        /**
         * @var ActivationRule[] $_activation_rules
         */
        protected $_activation_rules = [];

        /**
         * Network constructor.
         *
         * @param string|null $filename
         */
        function __construct($filename = null) {
            if (!is_null($filename)) {
                $this->load_network($filename);
            }
        }

        /**
         * @var string|null
         */
        protected $_file_buffer = null;

        /**
         * @param string $filename
         *
         * @throws NetworkException
         */
        function load_network($filename) {
            if (!file_exists($filename)) {
                throw new NetworkException('File "'.$filename.'" does not exist', 5);
            } elseif (!is_readable($filename)) {
                throw new NetworkException('File "'.$filename.'" is not readable', 6);
            }
            try {
                $this->load_network_from_json($filename);

                return;
            } catch (NetworkException $e) {
                if ($e->getCode() != 1) {
                    // @codeCoverageIgnoreStart
                    throw $e;
                    // @codeCoverageIgnoreEnd
                }
            }
            $this->load_network_from_bin_buffer();
        }

        /**
         * @param string $filename
         *
         * @throws NetworkException
         */
        function load_network_from_json($filename) {
            if (!file_exists($filename)) {
                throw new NetworkException('File "'.$filename.'" does not exist', 5);
            } elseif (!is_readable($filename)) {
                throw new NetworkException('File "'.$filename.'" is not readable', 6);
            }
            $this->_file_buffer = file_get_contents($filename);
            if ($this->_file_buffer === false) {
                // @codeCoverageIgnoreStart
                throw new NetworkException('Can not read file "'.$filename.'"', 7);
                // @codeCoverageIgnoreEnd
            }
            $data = json_decode($this->_file_buffer);
            if (is_null($data)) {
                throw new NetworkException('File "'.$filename.'" does not contain json', 1);
            }

            $this->_geometry = $data->geometry;
            $this->set_weights($data->weigths);
            $this->_activation_rules = [null];
            foreach ($data->activation_rule as $rule_type) {
                $this->_activation_rules[] = new ActivationRule($rule_type);
                // @todo дописать
            }
        }

        /**
         * @param string $filename
         *
         * @throws NetworkException
         */
        function load_network_from_bin($filename) {
            if (!file_exists($filename)) {
                throw new NetworkException('File "'.$filename.'" does not exist', 5);
            } elseif (!is_readable($filename)) {
                throw new NetworkException('File "'.$filename.'" is not readable', 6);
            }
            $this->_file_buffer = file_get_contents($filename);
            if ($this->_file_buffer === false) {
                // @codeCoverageIgnoreStart
                throw new NetworkException('Can not read file "'.$filename.'"', 7);
                // @codeCoverageIgnoreEnd
            }
            $this->_file_buffer_offset = 0;
            $this->load_network_from_bin_buffer();
        }

        /**
         * Загружаем сеть из внутреннего буффера
         * @throws NetworkException
         */
        protected function load_network_from_bin_buffer() {
            if ($this->file_buffer_get(4) != 'NKNN') {
                throw new NetworkException('Malformed neural network file', 9);
            }

            $layer_count = $this->file_buffer_get_integer();
            $this->_geometry = [];
            for ($i = 0; $i < $layer_count; $i++) {
                $this->_geometry[] = $this->file_buffer_get_integer();
            }

            $weight_number = $this->get_weights_number();
            $weights = [];
            for ($i = 0; $i < $weight_number; $i++) {
                $weights[] = $this->file_buffer_get_double();
            }
            $this->set_weights($weights);

            // Читаем rules
            $this->_activation_rules = array_fill(0, count($this->_geometry), null);
            for ($layer = 1; $layer < count($this->_geometry); $layer++) {
                $rule_type = $this->file_buffer_get_integer();
                switch ($rule_type) {
                    case ActivationRule::TYPE_SINGLE_TANH:
                    case ActivationRule::TYPE_SINGLE_SIGMOID:
                    case ActivationRule::TYPE_SINGLE_SOFTMAX:
                        $this->_activation_rules[$layer] = new ActivationRule($rule_type);
                        break;
                    // @todo Дописать
                    // @codeCoverageIgnoreStart
                    default:
                        throw new NetworkException('Временно выключено');
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        /**
         * @var integer
         */
        protected $_file_buffer_offset = 0;

        /**
         * @param integer $length_bytes
         *
         * @return string
         */
        protected function file_buffer_get($length_bytes) {
            $offset = $this->_file_buffer_offset;
            $this->_file_buffer_offset += $length_bytes;

            return substr($this->_file_buffer, $offset, $length_bytes);
        }

        /**
         * @return integer
         */
        protected function file_buffer_get_integer() {
            return unpack('V', $this->file_buffer_get(4))[1];
        }

        /**
         * @return double
         */
        protected function file_buffer_get_double() {
            return unpack('d', $this->file_buffer_get(8))[1];
        }

        /**
         * @param string         $filename
         * @param boolean|string $force_type json — для js, bin — для всего остального, false — для автовыбора
         *
         * @throws NetworkException
         */
        function save_network($filename, $force_type = false) {
            if ($force_type === false) {
                if (mb_strtolower(mb_substr($filename, -5)) == '.json') {
                    $type = 'json';
                } else {
                    $type = 'bin';
                }
            } else {
                if (!in_array($force_type, ['json', 'bin'])) {
                    throw new NetworkException('Malformed type for save network', 8);
                }
                $type = $force_type;
            }

            if ($type == 'json') {
                $this->save_network_to_json($filename);
            } else {
                $this->save_network_to_bin($filename);
            }
        }

        /**
         * @param string $filename
         *
         * @throws NetworkException
         */
        protected function save_network_to_json($filename) {
            $data = (object) [];
            $data->geometry = $this->_geometry;
            $data->weigths = $this->get_weights();
            $data->activation_rule = [];
            foreach ($this->_activation_rules as $num => &$rule) {
                if ($num === 0) {
                    continue;
                }
                if (is_null($rule)) {
                    // @hint Такого быть не должно
                    // @codeCoverageIgnoreStart
                    $data->activation_rule[] = null;
                    continue;
                    // @codeCoverageIgnoreEnd
                }

                $rule_type = $rule->get_type();
                switch ($rule_type) {
                    case ActivationRule::TYPE_SINGLE_TANH:
                    case ActivationRule::TYPE_SINGLE_SIGMOID:
                    case ActivationRule::TYPE_SINGLE_SOFTMAX:
                        $data->activation_rule[] = $rule_type;
                        break;
                    // @todo Дописать
                    // @codeCoverageIgnoreStart
                    default:
                        throw new NetworkException('Временно выключено');
                    // @codeCoverageIgnoreEnd
                }
            }

            file_put_contents($filename, json_encode($data), LOCK_EX);
        }

        /**
         * @param string $filename
         *
         * @throws NetworkException
         */
        protected function save_network_to_bin($filename) {
            $buf = 'NKNN'.pack('V', count($this->_geometry));
            foreach ($this->_geometry as &$length) {
                $buf .= pack('V', $length);
            }
            $weights = $this->get_weights();
            foreach ($weights as &$weight) {
                $buf .= pack('d', $weight);
            }
            unset($length, $weight, $weights);
            for ($layer = 1; $layer < count($this->_geometry); $layer++) {
                $rule = $this->_activation_rules[$layer];
                $rule_type = $rule->get_type();
                switch ($rule_type) {
                    case ActivationRule::TYPE_SINGLE_TANH:
                    case ActivationRule::TYPE_SINGLE_SIGMOID:
                    case ActivationRule::TYPE_SINGLE_SOFTMAX:
                        $buf .= pack('V', $rule_type);
                        break;
                    // @todo Дописать
                    // @codeCoverageIgnoreStart
                    default:
                        throw new NetworkException('Временно выключено');
                    // @codeCoverageIgnoreEnd
                }
            }

            file_put_contents($filename, $buf, LOCK_EX);
        }

        /**
         * Считаем всю сеть
         *
         * @param double[] $input
         *
         * @return double[]
         * @throws NetworkException
         */
        function calculate(array $input) {
            if (count($input) != $this->_geometry[0]) {
                throw new NetworkException('Malformed input for calculation', 2);
            }
            $this->_values = $this->_biases;
            $this->_values[0] = $input;

            for ($layer = 1; $layer < count($this->_geometry); $layer++) {
                for ($i = 0; $i < $this->_geometry[$layer]; $i++) {
                    for ($j = 0; $j < $this->_geometry[$layer - 1]; $j++) {
                        $this->_values[$layer][$i] += $this->_values[$layer - 1][$j] * $this->_weights[$layer][$i][$j];
                    }
                }

                $this->_activation_rules[$layer]->calculate($this->_values[$layer]);
            }

            return $this->_values[count($this->_geometry) - 1];
        }

        /**
         * @return double[][]
         */
        function get_layers_after_calculate() {
            return $this->_values;
        }

        /**
         * @return integer
         */
        function get_weights_number() {
            $count = 0;
            for ($i = 1; $i < count($this->_geometry); $i++) {
                $count += ($this->_geometry[$i - 1] + 1) * $this->_geometry[$i];
            }

            return $count;
        }

        /**
         * Читаем weights и biases всей сети
         *
         * @return double[]
         */
        function get_weights() {
            $data = [];
            for ($layerNum = 1; $layerNum < count($this->_geometry); $layerNum++) {
                for ($i = 0; $i < $this->_geometry[$layerNum]; $i++) {
                    for ($j = 0; $j < $this->_geometry[$layerNum - 1]; $j++) {
                        $data[] = $this->_weights[$layerNum][$i][$j];
                    }
                }
                for ($i = 0; $i < $this->_geometry[$layerNum]; $i++) {
                    $data[] = $this->_biases[$layerNum][$i];
                }
            }

            return $data;
        }

        /**
         * Пишем weights и biases всей сети
         *
         * @param double[] $weights
         *
         * @throws NetworkException
         */
        function set_weights(array $weights) {
            if (count($weights) != $this->get_weights_number()) {
                throw new NetworkException('Bad weights array', 3);
            }

            $k = 0;
            $this->_weights = [null];
            $this->_biases = [null];
            for ($layerNum = 1; $layerNum < count($this->_geometry); $layerNum++) {
                $this->_weights[$layerNum] = [];
                $this->_biases[$layerNum] = [];

                for ($i = 0; $i < $this->_geometry[$layerNum]; $i++) {
                    for ($j = 0; $j < $this->_geometry[$layerNum - 1]; $j++) {
                        $this->_weights[$layerNum][$i][$j] = $weights[$k++];
                    }
                }
                for ($i = 0; $i < $this->_geometry[$layerNum]; $i++) {
                    $this->_biases[$layerNum][$i] = $weights[$k++];
                }
            }
        }

        /**
         * @return integer[]
         */
        function get_geometry() {
            return $this->_geometry;
        }

        /**
         * @param integer[] $geometry
         */
        function set_geometry(array $geometry) {
            $this->_geometry = $geometry;
        }

        /**
         * @return integer
         */
        function get_layers_count() {
            return count($this->_geometry);
        }

        /**
         * @return ActivationRule[]
         */
        function get_activation_rules() {
            return $this->_activation_rules;
        }

        /**
         * @param ActivationRule[] $rules
         *
         * @throws NetworkException
         */
        function set_activation_rules(array $rules) {
            if (count($rules) != count($this->get_geometry())) {
                throw new NetworkException('Malformed activation rules', 4);
            }
            $this->_activation_rules = $rules;
        }
    }


?>