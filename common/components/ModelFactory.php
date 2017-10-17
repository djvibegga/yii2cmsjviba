<?php
/**
 * ModelFactory class file
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Marilev Evgeniy <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
namespace common\components;
/**
 * ModelFactory is the models factory manager class.
 *
 * PHP version 5
 *
 * @category   YII2-CMS
 * @package    Module.core
 * @subpackage Module.core.component
 * @author     Marilev Evgeniy <marilev@jviba.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://jviba.com/display/PhpDoc/yii-cms
 */
class ModelFactory extends Component
{
    /**
     * Custom models' properties
     * @var array
     */
    public $properties = array();
    
    /**
     * Custom models' behaviors
     * @var array
     */
    public $customBehaviors = array();

    /**
     * Redefinition real model class names used instead of cms models.
     * Example: array(
     *    'YUser' => 'MyCustomUser'
     * )
     * @var array
     */
    public $classMap = array();
    
    /**
     * Creates and returns new instance of cms model
     * 
     * @param string $className model class name or string presentation of operator 'new'
     * (for direct creating new model instance).
     * @param string $method    model's static method name or
     * model class name. Specify singleton method name used for instantiation.
     * For example for active records you can use 'model'.
     * @param array  $params    additional params used for instantiation.
     * 
     * @return \yii\base\Model|mixed model instance of static method call result
     */
    public function create($className, $method = null, array $params = null)
    {
        $isStaticUsage = $className !== 'new';
        if (!$isStaticUsage) {
            $className = $method;
        }
        $className = isset($this->classMap[$className])
                   ? $this->classMap[$className]
                   : $className; //this is copy of method resolveModelClassName (for high perfomance)
        $result = $isStaticUsage
               ? $this->modelStaticMethod($className, $method, $params)
               : $this->intantiateModel($className, $params);
        if (isset($result) && $result instanceof \yii\base\Component) {
            $this->initModelInstance($result);
        }
        return $result;
    }
    
    /**
     * Calls model's static method and returns result
     * @param string $className       model class name
     * @param string $singletonMethod model's method name
     * @param array  &$params         method parameters
     * @return mixed method call result
     */
    public function modelStaticMethod($className, $method, &$params)
    {
        $func = array($className, $method);
        if (empty($params)) {
            return call_user_func($func);
        } else {
            return call_user_func_array($func, $params);
        }
    }
    
    /**
     * Creates a model instance with parameters
     * 
     * @param string $className model class name
     * @param array  &$params   model contructor parameters
     * 
     * @return CComponent model instance
     */
    public function intantiateModel($className, &$params)
    {
        if (empty($params)) {
            $model = new $className();
        } else {
            switch (count($params)) {
            case 1:
                $model = new $className($params[0]);
                break;
            case 2:
                $model = new $className($params[0], $params[1]);
                break;
            case 3:
                $model = new $className($params[0], $params[1], $params[2]);
                break;
            }
        }
        return $model;
    }
    
    /**
     * Resolves real model class name.
     * 
     * @param string $className model class name to check.
     * 
     * @return string real model class name used.
     */
    public function resolveModelClassName($className)
    {
        return isset($this->classMap[$className]) ? $this->classMap[$className] : $className;
    }

    /**
     * Finds the YCMS model class name (alias) by its real name, used at the moment
     *
     * @param string $className The real class name used
     *
     * @return string The YCMS-default model class name
     */
    public function resolveModelClassAlias($className)
    {
        $realClassName = array_search($className, $this->classMap);
        return $realClassName === false ? $className : $realClassName;
    }

    /**
     * Initializes model instance after it's constructing
     * 
     * @param \yii\base\Component $instance model instance
     * 
     * @return void
     */
    public function initModelInstance(\yii\base\Component $instance)
    {
        $this->initProperties($instance);
        $this->initBehaviors($instance);
    }
    
    /**
     * Initializes model instance by additional custom properties
     * @param \yii\base\Component $instance model instance
     * @return void
     */
    protected function initProperties(\yii\base\Component $instance)
    {
        foreach ($this->properties as $className => $properties) {
            if ($instance instanceof $className) {
                foreach ($properties as $name => $value) {
                    $instance->$name = $value;
                }
            }
        }
    }
    
    /**
     * Initializes model instance by additional behaviors
     * @param \yii\base\Component $instance model instance
     * @return void
     */
    protected function initBehaviors(\yii\base\Component $instance)
    {
        foreach ($this->customBehaviors as $className => $behaviors) {
            if ($instance instanceof $className) {
                foreach ($behaviors as $name => $value) {
                    $instance->attachBehavior($name, $value);
                }
            }
        }
    }
}