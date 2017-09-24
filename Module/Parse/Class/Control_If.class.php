<?php

namespace Priya\Module\Parse;

class Control_If extends Core {
    const MAX = 2;

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
    }

    public static function has($list=array()){
        return (bool) Control_If::get($list);
    }

    public static function get($list=array()){
        foreach($list as $nr => $record){
            $tag = key($record);
            if(substr($tag, 0, 3) == '{if'){
                return $tag;
            }
        }
        return false;
    }

    public static function create($list=array(), $string=''){
        $result = array();
        $result['string'] = $string;
        $if = Control_If::get($list);
        $explode = explode($if, $string, 2);
        $inner_raw = $explode[1];
        $explode = explode('{if', $inner_raw);

        $list = array();
        foreach($explode as $nr => $record){
            $item = array();
            $item['if_open_count'] = $nr + 1;
            $item['if_close_count'] = substr_count($record, '{/if}');
            if($nr == 0){
                $item['part'] = $if . $record;
            } else {
                $item['part'] = '{if' . $record;
            }
            $list[] = $item;
            if($item['if_open_count'] == $item['if_close_count']){
                $value = '';
                foreach($list as $list_nr => $list_record){
                    $temp = explode('{/if}', $list_record['part']);
                    if(count($temp) > 1){
                        array_pop($temp);
                        $value .= implode('{/if}', $temp) . '{/if}';
                    } else {
                        $value .= $list_record['part'];
                    }
                }
                $result['if']['value'] = $value;
            }
        }
        $result['if']['statement'] = $if;
        return $result;
    }

    /**
     * - create left right
     * - create true & false
     * - execute statement see operator::statement
     */
    public function statement($record=array()){
        $create = Token::restore_return($record['if']['statement'], $this->random());
        $create = str_replace('{if ', '', substr($create, 0, -1));
        $variable = new Variable($this->data(), $this->random());
        // an equation can be a variable, if it is undefined it will be + 0
//         debug($create, 'create');
        $parse = Token::parse($create);
        $parse = Token::variable($parse, $variable);
        $math = Token::create_equation($parse);
        $record['if']['result'] = $math;
        if($record['if']['result'] === true){
            //get the true part of the if statement
            //replace the if statement with the true part of the statement in record['string']
        }
        elseif($record['if']['result'] === false){
            //get the else part of the if statement or null without else
            //replace the if statement with the else part of the statement in record['string'] or null
        } else {
            debug($record, 'unknown result should be true || false');
            die;
        }
    }
}