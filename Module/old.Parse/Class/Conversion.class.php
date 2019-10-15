<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Parse;

use Priya\Application;
use Priya\Module\Core\Main;
use stdClass;

class Conversion extends Main {

    const DIR = __DIR__;
    const FILE = __FILE__;

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
        $called = get_called_class();
        $dir = dirname($called::DIR);
        $this->data('module.route.dir', clone $this->data('module.dir'));
        $this->data('module.dir', new stdClass());

        $this->data('module.dir.root', $dir . Application::DS);
        $this->data('module.dir.data', $this->data('module.dir.root') . 'Data' . Application::DS);
        $this->data('module.dir.public', $this->data('module.dir.root') . $this->data('public_html') . Application::DS);
        $this->data('module.dir.view', $this->data('module.dir.root') . Conversion::VIEW . Application::DS);
        $this->data('module.dir.function', $this->data('module.dir.root') . ucfirst(Token::TYPE_FUNCTION) . Application::DS);
        $this->data('module.dir.modifier', $this->data('module.dir.root') . ucfirst(Token::TYPE_MODIFIER) . Application::DS);
        $this->data('module.dir.translation', $this->data('module.dir.root') . ucfirst(Conversion::TRANSLATION) . Application::DS);
        $this->data('module.dir.class', $this->data('module.dir.root') . ucfirst(Token::TYPE_CLASS) . Application::DS);        
        $this->data('module.dir.trait', $this->data('module.dir.root') . ucfirst(Token::TYPE_TRAIT) . Application::DS);
    }
}