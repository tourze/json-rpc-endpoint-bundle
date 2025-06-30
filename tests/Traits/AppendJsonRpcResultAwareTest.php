<?php

namespace Tourze\JsonRPCEndpointBundle\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPCEndpointBundle\Tests\Fixtures\TestClassWithAppendJsonRpcResultAware;

class AppendJsonRpcResultAwareTest extends TestCase
{
    private TestClassWithAppendJsonRpcResultAware $testObject;

    protected function setUp(): void
    {
        $this->testObject = new TestClassWithAppendJsonRpcResultAware();
    }

    public function testGetJsonRpcResult_withDefaultValue_returnsEmptyArray(): void
    {
        $result = $this->testObject->getJsonRpcResult();

        $this->assertSame([], $result);
    }

    public function testSetJsonRpcResult_withValidArray_setsResult(): void
    {
        $data = ['key1' => 'value1', 'key2' => 'value2'];

        $this->testObject->setJsonRpcResult($data);

        $this->assertSame($data, $this->testObject->getJsonRpcResult());
    }

    public function testSetJsonRpcResult_withEmptyArray_setsEmptyResult(): void
    {
        $this->testObject->setJsonRpcResult([]);

        $this->assertSame([], $this->testObject->getJsonRpcResult());
    }

    public function testSetJsonRpcResult_withComplexArray_preservesStructure(): void
    {
        $complexData = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ],
            'meta' => [
                'total' => 2,
                'pagination' => ['page' => 1, 'size' => 10]
            ],
            'settings' => [
                'debug' => true,
                'version' => '1.0.0'
            ]
        ];

        $this->testObject->setJsonRpcResult($complexData);

        $this->assertSame($complexData, $this->testObject->getJsonRpcResult());
    }

    public function testSetJsonRpcResult_overwritesPreviousValue(): void
    {
        $initialData = ['initial' => 'data'];
        $newData = ['new' => 'data'];

        $this->testObject->setJsonRpcResult($initialData);
        $this->assertSame($initialData, $this->testObject->getJsonRpcResult());

        $this->testObject->setJsonRpcResult($newData);
        $this->assertSame($newData, $this->testObject->getJsonRpcResult());
    }

    public function testSetJsonRpcResult_withNumericArray_preservesIndexes(): void
    {
        $numericData = [1, 2, 3, 'four', 5.5];

        $this->testObject->setJsonRpcResult($numericData);

        $this->assertSame($numericData, $this->testObject->getJsonRpcResult());
    }

    public function testSetJsonRpcResult_withNestedArrays_preservesDepth(): void
    {
        $nestedData = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'deep' => 'value'
                    ]
                ]
            ]
        ];

        $this->testObject->setJsonRpcResult($nestedData);

        $this->assertSame($nestedData, $this->testObject->getJsonRpcResult());
        $this->assertSame('value', $this->testObject->getJsonRpcResult()['level1']['level2']['level3']['deep']);
    }

    public function testGetAndSetJsonRpcResult_maintainReferences(): void
    {
        $data = ['key' => 'value'];
        
        $this->testObject->setJsonRpcResult($data);
        $retrievedData = $this->testObject->getJsonRpcResult();
        
        // 修改原始数组不应该影响已设置的数据（值拷贝，不是引用）
        $data['key'] = 'modified';
        
        $this->assertSame('value', $retrievedData['key']);
        $this->assertSame('value', $this->testObject->getJsonRpcResult()['key']);
    }

    public function testSetJsonRpcResult_withMixedTypes_preservesTypes(): void
    {
        $mixedData = [
            'string' => 'text',
            'integer' => 42,
            'float' => 3.14,
            'boolean' => true,
            'null' => null,
            'array' => [1, 2, 3],
            'object' => ['nested' => 'value']
        ];

        $this->testObject->setJsonRpcResult($mixedData);
        $result = $this->testObject->getJsonRpcResult();

        $this->assertSame('text', $result['string']);
        $this->assertSame(42, $result['integer']);
        $this->assertSame(3.14, $result['float']);
        $this->assertTrue($result['boolean']);
        $this->assertNull($result['null']);
        $this->assertSame([1, 2, 3], $result['array']);
        $this->assertSame(['nested' => 'value'], $result['object']);
    }
} 