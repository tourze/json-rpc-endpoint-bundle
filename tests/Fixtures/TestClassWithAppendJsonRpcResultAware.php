<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Fixtures;

use Tourze\JsonRPCEndpointBundle\Traits\AppendJsonRpcResultAware;

/**
 * 创建一个使用AppendJsonRpcResultAware trait的测试类
 */
class TestClassWithAppendJsonRpcResultAware
{
    use AppendJsonRpcResultAware;
}