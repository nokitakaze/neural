<?php

    abstract class AbstractNetworkTest extends \PHPUnit_Framework_TestCase {
        function dataPHP7TypeHinting() {
            if (version_compare(PHP_VERSION, '7.0.0', '<')) {
                return [];
            }

            $class = '\\NokitaKaze\\Neural\\'.substr(static::class, 0, -4);
            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods();
            $data = [];
            foreach ($methods as $method) {
                $data[] = [
                    'method' => $method->getName(),
                ];
            }

            return $data;
        }

        /**
         * @param string $method
         *
         * @dataProvider dataPHP7TypeHinting
         */
        function testPHP7TypeHinting($method = null) {
            if (is_null($method)) {
                return;
            }
            $class = '\\NokitaKaze\\Neural\\'.substr(static::class, 0, -4);
            $reflection = new ReflectionMethod($class, $method);
            if ($reflection->hasReturnType()) {
                return;
            }
            foreach ($reflection->getParameters() as $parameter) {
                if ($parameter->hasType()) {
                    return;
                }
            }
            $this->markTestIncomplete('Type hinting missing');
        }
    }

?>