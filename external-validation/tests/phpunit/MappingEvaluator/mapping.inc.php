<?php
$mapping = array(
    'testcase one' => array(
        'nodeSelector' => '/test/testcase[@case="one" and @result="true"]'
    ),
    'testcase two' => array(
        'nodeSelector' => '/test/testcase[@case="two"]/result',
        'valueFormatter' => 'concat(substring-after(./text(), "."), substring-before(./text(), "."))'
    ),
    'testcase three' => array(
        'nodeSelector' => '/test/testcase[@case="tree"]/result'
    )
);