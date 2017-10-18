<?php
/**
 * RecacheController class file
 * 
 * PHP version 5
 *
 * @category  Packages
 * @package   Console.command
 * @author    Dmitry Cherepovsky <cherep@jviba.com>
 * @author    Marilev Evgeniy <marilev@jviba.com>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://jviba.com/display/PhpDoc/yii-cms
 */
namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\CMS;
use yii\helpers\Console;

/**
 * RecacheController sets the global recaching flags for the given cachable 
 * data source class names.
 * 
 * PHP version 5
 *
 * @category  Packages
 * @package   Console.command
 * @author    Dmitry Cherepovsky <cherep@jviba.com>
 * @author    Marilev Evgeniy <marilev@jviba.com>
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      https://jviba.com/display/PhpDoc/yii-cms
 */
class RecacheController extends Controller
{
    const ERROR_NO_FACTORY_COMPONENT = 'CacheAdapterFactory component not attached or disabled';
    
    /**
     * Default action
     * @var string
     */
    public $defaultAction = 'exec-pull';
    
    /**
     * Predefined data sources' options
     * @var array
     */
    public $sources = array();
    
    /**
     * @var string
     */
    public $targetSrc;
    
    /**
     * {@inheritDoc}
     * @see \yii\console\Controller::options()
     */
    public function options($actionID)
    {
        if ($actionID == 'exec-pull') {
            return ['targetSrc'];
        }
        return [];
    }
    
    /**
     * (non-PHPdoc)
     * 
     * @return void
     * @see CConsoleCommand::getHelp()
     */
    public function getHelp()
    {
        echo <<<EOD
        USAGE
  yii recache/[action] [parameters]
    
DESCRIPTION
  This command provides console interface for working with cache adapters,
  processing recache sources data.
  If the 'action' parameter is not given, it defaults to 'execPull'.
  Each action takes different parameters. Their usage can be found in
  the following examples.
    
EXAMPLES
  
 * yii recache/exec-pull --sources=MyDataSourceClassName1,MyDataSourceClassName2
   Applies cache sources force pull process. When sources data will be retrieved
   cache adapter executes full datasource pull. When this process will be completed
   all sources contains full data pull in the cache.
    
EOD;
    }
    
    /**
     * Executes the command.
     * @return void
     */
    public function actionExecPull()
    {
        $factory = Yii::$app->get('cacheAdapterFactory');
        if ($factory === null) {
            $this->usageError(self::ERROR_NO_FACTORY_COMPONENT);
        }
        foreach (explode(',', $this->targetSrc) as $dataSourceClassName) {
            if (isset($this->sources[$dataSourceClassName])) {
                $config = $this->sources[$dataSourceClassName];
                $config['class'] = $dataSourceClassName;
            } else {
                $config = ['class' => $dataSourceClassName];
            }
            $numericKeys = array();
            foreach ($config as $key => $value) {
                if (is_int($key)) {
                    $numericKeys[] = $value;
                    unset($config[$key]);
                }
            }
            $params = array_merge($config, $numericKeys);
            $source = Yii::createObject($params);
            $params = ['CLASS' => $dataSourceClassName];
            try {
                $message = 'Started executing pull for data source "{CLASS}"';
                Console::output(Yii::t('app', $message, $params));
                $adapter = $factory->createFromSource($source);
                $adapter->pull($source, true);
                $message = 'Successfully executed pull for data source "{CLASS}"';
            } catch (Exception $e) {
                $params['CAUSE'] = $e->getMessage();
                $message = 'ATTENTION! Can\'t execute pull for data source "{CLASS}", because: {CAUSE}';
            }
            Console::output(Yii::t('app', $message, $params));
        }
    }
}