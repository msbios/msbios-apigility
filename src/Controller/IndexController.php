<?php
/**
 * @access protected
 * @author Judzhin Miles <info[woof-woof]msbios.com>
 */

namespace MSBios\Apigility\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF\Apigility\Admin\Module as AdminModule;

/**
 * Class IndexController
 * @package MSBios\Apigility\Controller
 */
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        if (class_exists(AdminModule::class, false)) {
            return $this->redirect()->toRoute('zf-apigility/ui');
        }
        return new ViewModel();
    }
}
