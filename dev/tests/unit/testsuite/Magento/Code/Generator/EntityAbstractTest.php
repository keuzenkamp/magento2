<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Code
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Code_Generator_EntityAbstractTest extends PHPUnit_Framework_TestCase
{
    /**#@+
     * Source and result class parameters
     */
    const SOURCE_CLASS     = 'Varien_Object';
    const RESULT_CLASS     = 'Varien_Object_MyResult';
    const RESULT_FILE      = 'MyResult/MyResult.php';
    const RESULT_DIRECTORY = 'MyResult';
    /**#@-*/

    /**
     * Basic code generation directory
     */
    const GENERATION_DIRECTORY = 'generation';

    /**#@+
     * Generated code before and after code style fix
     */
    const SOURCE_CODE = "a = 1; b = array (); {\n\n some source code \n\n}";
    const RESULT_CODE = "a = 1; b = array(); {\n some source code \n}";
    /**#@-*/

    /**
     * Model under test
     *
     * @var Magento_Code_Generator_EntityAbstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Magento_Code_Generator_EntityAbstract');
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    public function testConstruct()
    {
        // without parameters
        $this->assertAttributeEmpty('_sourceClassName', $this->_model);
        $this->assertAttributeEmpty('_resultClassName', $this->_model);
        $this->assertAttributeInstanceOf('Magento_Code_Generator_Io', '_ioObject', $this->_model);
        $this->assertAttributeInstanceOf('Magento_Code_Generator_CodeGenerator_Zend', '_classGenerator', $this->_model);
        $this->assertAttributeInstanceOf('Magento_Autoload_IncludePath', '_autoloader', $this->_model);

        // with source class name
        $this->_model = $this->getMockForAbstractClass(
            'Magento_Code_Generator_EntityAbstract', array(self::SOURCE_CLASS)
        );
        $this->assertAttributeEquals(self::SOURCE_CLASS, '_sourceClassName', $this->_model);
        $this->assertAttributeEquals(self::SOURCE_CLASS . 'Abstract', '_resultClassName', $this->_model);

        // with all arguments
        $ioObject      = $this->getMock('Magento_Code_Generator_Io', array(), array(), '', false);
        $codeGenerator = $this->getMock('Magento_Code_Generator_CodeGenerator_Zend', array(), array(), '', false);
        $autoloader    = $this->getMock('Magento_Autoload_IncludePath', array(), array(), '', false);

        $this->_model = $this->getMockForAbstractClass(
            'Magento_Code_Generator_EntityAbstract',
            array(self::SOURCE_CLASS, self::RESULT_CLASS, $ioObject, $codeGenerator, $autoloader)
        );
        $this->assertAttributeEquals(self::RESULT_CLASS, '_resultClassName', $this->_model);
        $this->assertAttributeEquals($ioObject, '_ioObject', $this->_model);
        $this->assertAttributeEquals($codeGenerator, '_classGenerator', $this->_model);
        $this->assertAttributeEquals($autoloader, '_autoloader', $this->_model);
    }

    /**
     * Data provider for testGenerate method
     *
     * @return array
     */
    public function generateDataProvider()
    {
        return array(
            'no_source_class' => array(
                '$errors'           => array('Source class ' . self::SOURCE_CLASS . ' doesn\'t exist.'),
                '$isGeneration'     => false,
                '$classExistsFirst' => false,
            ),
            'result_class_exists' => array(
                '$errors'            => array('Result class ' . self::RESULT_CLASS . ' already exists.'),
                '$isGeneration'      => false,
                '$classExistsFirst'  => true,
                '$classExistsSecond' => true,
            ),
            'cant_create_generation_directory' => array(
                '$errors'            => array('Can\'t create directory ' . self::GENERATION_DIRECTORY . '.'),
                '$isGeneration'      => false,
                '$classExistsFirst'  => true,
                '$classExistsSecond' => false,
                '$makeGeneration'    => false,

            ),
            'cant_create_result_directory' => array(
                '$errors'            => array('Can\'t create directory ' . self::RESULT_DIRECTORY . '.'),
                '$isGeneration'      => false,
                '$classExistsFirst'  => true,
                '$classExistsSecond' => false,
                '$makeGeneration'    => true,
                '$makeResultFile'    => false
            ),
            'result_file_exists' => array(
                '$errors'            => array('Result file ' . self::RESULT_FILE . ' already exists.'),
                '$isGeneration'      => false,
                '$classExistsFirst'  => true,
                '$classExistsSecond' => false,
                '$makeGeneration'    => true,
                '$makeResultFile'    => true,
                '$fileExists'        => true,
            ),
            'generate_no_data' => array(
                '$errors' => array('Can\'t generate source code.'),
                '$isGeneration'      => true,
                '$classExistsFirst'  => true,
                '$classExistsSecond' => false,
                '$makeGeneration'    => true,
                '$makeResultFile'    => true,
                '$fileExists'        => true,
                '$isValid'           => false
            ),
            'generate_ok' => array(),
        );
    }

    /**
     * @param array $errors
     * @param bool $isGeneration
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     * @param bool $isValid
     *
     * @dataProvider generateDataProvider
     * @covers Magento_Code_Generator_EntityAbstract::generate
     * @covers Magento_Code_Generator_EntityAbstract::getErrors
     * @covers Magento_Code_Generator_EntityAbstract::_getSourceClassName
     * @covers Magento_Code_Generator_EntityAbstract::_getResultClassName
     * @covers Magento_Code_Generator_EntityAbstract::_getDefaultResultClassName
     * @covers Magento_Code_Generator_EntityAbstract::_generateCode
     * @covers Magento_Code_Generator_EntityAbstract::_addError
     * @covers Magento_Code_Generator_EntityAbstract::_validateData
     * @covers Magento_Code_Generator_EntityAbstract::_getClassDocBlock
     * @covers Magento_Code_Generator_EntityAbstract::_getGeneratedCode
     * @covers Magento_Code_Generator_EntityAbstract::_fixCodeStyle
     */
    public function testGenerate(
        $errors = array(),
        $isGeneration = true,
        $classExistsFirst = true,
        $classExistsSecond = false,
        $makeGeneration = true,
        $makeResultFile = true,
        $fileExists = false,
        $isValid = true
    ) {
        if ($isGeneration) {
            $arguments = $this->_prepareMocksForGenerateCode($isValid);
        } else {
            $arguments = $this->_prepareMocksForValidateData(
                $classExistsFirst,
                $classExistsSecond,
                $makeGeneration,
                $makeResultFile,
                $fileExists
            );
        }
        $abstractGetters = array('_getClassProperties', '_getClassMethods');
        $this->_model = $this->getMockForAbstractClass(
            'Magento_Code_Generator_EntityAbstract', $arguments, '', true, true, true, $abstractGetters
        );
        // we need to mock abstract methods to set correct return value type
        foreach ($abstractGetters as $methodName) {
            $this->_model->expects($this->any())
                ->method($methodName)
                ->will($this->returnValue(array()));
        }

        $result = $this->_model->generate();
        if ($errors) {
            $this->assertFalse($result);
            $this->assertEquals($errors, $this->_model->getErrors());
        } else {
            $this->assertTrue($result);
            $this->assertEmpty($this->_model->getErrors());
        }
    }

    /**
     * Prepares mocks for validation verification
     *
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     * @return array
     */
    protected function _prepareMocksForValidateData(
        $classExistsFirst = true,
        $classExistsSecond = false,
        $makeGeneration = true,
        $makeResultFile = true,
        $fileExists = false
    ) {
        $ioObject = $this->getMock('Magento_Code_Generator_Io',
            array(
                'getResultFileName',
                'makeGenerationDirectory',
                'makeResultFileDirectory',
                'fileExists',
                'getGenerationDirectory',
                'getResultFileDirectory',
                'writeResultFile'
            ),
            array(), '', false
        );
        $autoloader = $this->getMock('Magento_Autoload_IncludePath', array('getFile'), array(), '', false);

        $ioObject->expects($this->any())
            ->method('getResultFileName')
            ->with(self::RESULT_CLASS)
            ->will($this->returnValue(self::RESULT_FILE));
        $ioObject->expects($this->any())
            ->method('getGenerationDirectory')
            ->will($this->returnValue(self::GENERATION_DIRECTORY));
        $ioObject->expects($this->any())
            ->method('getResultFileDirectory')
            ->will($this->returnValue(self::RESULT_DIRECTORY));

        $autoloader->staticExpects($this->at(0))
            ->method('getFile')
            ->with(self::SOURCE_CLASS)
            ->will($this->returnValue($classExistsFirst));
        if ($classExistsFirst) {
            $autoloader->staticExpects($this->at(1))
                ->method('getFile')
                ->with(self::RESULT_CLASS)
                ->will($this->returnValue($classExistsSecond));
        }

        $expectedInvocations = 1;
        if ($classExistsFirst) {
            $expectedInvocations = 2;
        }
        $autoloader->staticExpects($this->exactly($expectedInvocations))
            ->method('getFile');

        $expectedInvocations = 1;
        if (!$classExistsFirst || $classExistsSecond) {
            $expectedInvocations = 0;
        }
        $ioObject->expects($this->exactly($expectedInvocations))
            ->method('makeGenerationDirectory')
            ->will($this->returnValue($makeGeneration));

        $this->_prepareIoObjectExpectations(
            $ioObject,
            $classExistsFirst,
            $classExistsSecond,
            $makeGeneration,
            $makeResultFile,
            $fileExists
        );

        return array(
            'source_class'   => self::SOURCE_CLASS,
            'result_class'   => self::RESULT_CLASS,
            'io_object'      => $ioObject,
            'code_generator' => null,
            'autoloader'     => $autoloader
        );
    }

    /**
     * @param $ioObject PHPUnit_Framework_MockObject_MockObject
     * @param bool $classExistsFirst
     * @param bool $classExistsSecond
     * @param bool $makeGeneration
     * @param bool $makeResultFile
     * @param bool $fileExists
     */
    protected function _prepareIoObjectExpectations(
        $ioObject,
        $classExistsFirst,
        $classExistsSecond,
        $makeGeneration,
        $makeResultFile,
        $fileExists
    ) {
        if ($classExistsFirst && !$classExistsSecond && $makeGeneration) {
            $ioObject->expects($this->once())
                ->method('makeResultFileDirectory')
                ->with(self::RESULT_CLASS)
                ->will($this->returnValue($makeResultFile));
        }

        if ($classExistsFirst && !$classExistsSecond && $makeGeneration && $makeResultFile) {
            $ioObject->expects($this->once())
                ->method('fileExists')
                ->with(self::RESULT_FILE)
                ->will($this->returnValue($fileExists));
        }
    }

    /**
     * Prepares mocks for code generation test
     *
     * @param bool $isValid
     * @return array
     */
    protected function _prepareMocksForGenerateCode($isValid)
    {
        $mocks = $this->_prepareMocksForValidateData();

        $codeGenerator = $this->getMock('Magento_Code_Generator_CodeGenerator_Zend',
            array('setName', 'addProperties', 'addMethods', 'setClassDocBlock', 'generate'), array(), '', false
        );
        $codeGenerator->expects($this->once())
            ->method('setName')
            ->with(self::RESULT_CLASS)
            ->will($this->returnSelf());
        $codeGenerator->expects($this->once())
            ->method('addProperties')
            ->will($this->returnSelf());
        $codeGenerator->expects($this->once())
            ->method('addMethods')
            ->will($this->returnSelf());
        $codeGenerator->expects($this->once())
            ->method('setClassDocBlock')
            ->with($this->isType('array'))
            ->will($this->returnSelf());

        $codeGenerator->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($isValid ? self::SOURCE_CODE : null));

        /** @var $ioObject PHPUnit_Framework_MockObject_MockObject */
        $ioObject = $mocks['io_object'];
        if ($isValid) {
            $ioObject->expects($this->once())
                ->method('writeResultFile')
                ->with(self::RESULT_FILE, self::RESULT_CODE);
        }

        return array(
            'source_class'   => $mocks['source_class'],
            'result_class'   => $mocks['result_class'],
            'io_object'      => $ioObject,
            'code_generator' => $codeGenerator,
            'autoloader'     => $mocks['autoloader'],
        );
    }
}
