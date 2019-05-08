<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
// use Priya\Module\File;
// use Priya\Module\File\Dir;
// use Priya\Module\File\Extension;
// use Exception;
// use DateTime;

class PHPC extends Compile {        
    const DATA_PREFIX = 'priya.parse.compile.phpc.';
    const DATA_CONVERSION_ADD =  PHPC::DATA_PREFIX . 'conversion.add';
    const DATA_PROPERTY =  PHPC::DATA_PREFIX . 'property';

    public static function conversion_variable(
        Parse $parse,
        $parameter = [],
        &$token = [],
        $record = []
    ){        
        $attribute = key_exists(0, $parameter) ? $parameter[0] : [];   
        $data = key_exists(1, $parameter) ? $parameter[1] : null;   
        if(substr($attribute, 0, 1) == '$'){
            $attribute = substr($attribute, 1);
        }        
        $attributeList = Parse::explode_multi(Parse::ATTRIBUTE_EXPLODE, $attribute);
        //should be PHPC::data_property
        $variable_assign = $parse->data(Compile::DATA_VARIABLE_ASSIGN);
        if($variable_assign === null){
            $variable_assign = [];
        }
        $key = '$data->';
        $end = array_pop($attributeList);
        if(!in_array($attribute, $variable_assign)){
            $variable_assign[] = $attribute;
            $parse->data(Compile::DATA_VARIABLE_ASSIGN, $variable_assign);                              
            $data[] = PHPC::data_set($parse, [ $attribute, null ], $token, $record);     
        }  
        foreach($attributeList as $attribute_nr => $attribute_part){
            $key .= $attribute_part;                                                        
            $key .= '->';
        }                                        
        $key .= $end;  
        return $key;
    }

    public static function conversion_data(
        Parse $parse,
        $parameter = [],
        &$token = [],
        $method = []
    ){
        $indent = $parse->data(Compile::DATA_INDENT);
        $data = key_exists(0, $parameter) ? $parameter[0] : [];   
        $data_method = [];    


        //change this, if $data not is set ( in function, add function in register)...
        if($parse->data(PHPC::DATA_CONVERSION_ADD)){
            $data_method[] = Compile::string_tab($indent) . '$data = $parse->data();';
            $parse->data('delete', PHPC::DATA_CONVERSION_ADD);
        }
        $collect = '';
        $is_assign = false;
        $is_plus = false;
        $plus = '';

        var_dump($data);
        die;

        if(is_array($data)){
            foreach($data as $nr => $record){
                if(
                    $record['type'] == Token::TYPE_VARIABLE &&
                    $record['variable']['is_assign'] === true
                ){              
                    $is_assign = true;
                    if($collect !== ''){
                        $data_method[] = Compile::string_tab($indent) . 'function_echo($parse, [\'' . $collect . '\']);';  
                        $collect = '';
                    }       
                    $attribute = $record['variable']['name'];    

                    // convert variable value to string
                    // define undefined variables  from variable value first
                    // change + with Parse::plus()


                    $value = PHPC::variable_value($parse, [ $attribute, &$data_method ], $token, $record);




                    $key = PHPC::conversion_variable($parse, [ $attribute, &$data_method ], $token, $record);                    
                    // $value = PHPC::variable_value($parse, $data, $record, $attribute, $data_method);


                    /*
                    $set_key = $attribute;
                    if(substr($set_key, 0, 1) == '$'){
                        $set_key = substr($set_key, 1);
                    }
                    */

                    $data_method[] = PHPC::variable_init($parse, [ $attribute, &$data_method ], $token, $record);

                    // $data_method[] = Compile::string_tab($indent) . '$parse->data(\'set\', \'' . $set_key . '\', ' . PHPC::value_to_string($parse, null) . ');';
                    $data_method[] = Compile::string_tab($indent) . $key . ' = ' . $value . ';';  
                }	
                elseif(
                    $record['type'] == Token::TYPE_VARIABLE &&
                    $record['variable']['is_assign'] === false
                ){ 		
                    if($collect !== ''){
                        $data_method[] = Compile::string_tab($indent) . 'function_echo($parse, [\'' . $collect . '\']);';  
                        $collect = '';
                    }
                    if(!empty($record['variable']['has_modifier'])){
                        $attribute = substr($record['variable']['name'], 1);
                        $data_method[] = Compile::string_tab($indent) . '$modifier = $parse->data(\'' . $attribute . '\');';  
                        $compile_modifier = $parse->data(Compile::DATA_MODIFIER);
                        if($compile_modifier === null){
                            $compile_modifier = [];
                        }                    
                        foreach($record['variable']['modifier']['list'] as $modifier_nr => $modifier){
                            $filename = ucfirst(Token::TYPE_MODIFIER) . '.' . ucfirst($modifier['name']);
                            $function_name = strtolower(str_replace('.', '_', $filename));                        
                            $compile_modifier[$filename] = true;                    
                            $parameter = Compile::modifier_parameter($modifier);
                            $parameter = implode(',', $parameter);                                                                                                         
                            $data_method[] = Compile::string_tab($indent) . '$modifier = ' . $function_name . '($parse, $modifier,' . $parameter . ');';
                        }                    
                        $data_method[] = Compile::string_tab($indent) . 'function_echo($parse, [ $modifier ] );';  
                        $parse->data(Compile::DATA_MODIFIER, $compile_modifier);       
                    } else {
                        $attribute = substr($record['variable']['name'], 1);
                        $data_method[] = Compile::string_tab($indent) . 'function_echo($parse, [ $parse->data(\'' . $attribute . '\') ]);';  
                    }                
                }
                elseif($record['is_operator'] === true){    
                    var_Dump($record);
                    die;                
                    if($is_plus === true){                        
                        $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';             
                        $is_plus = false;
                        $plus = '';                                                                       
                    }
                    if($record['value'] == '+'){
                        $is_plus = true;                                
                        $plus = '';
                    } else {
                        $value .= $record['value'];                                
                    }                                                                                    
                }
                elseif(
                    $record['type'] == Token::TYPE_METHOD
                ){ 	
                    $filename = ucfirst(Token::TYPE_FUNCTION) . '.' . ucfirst($record['method']['name']);                
                    $function_name = strtolower(str_replace('.', '_', $filename));     
                    $compile_method = $parse->data(Compile::DATA_METHOD);
                    if($compile_method === null){
                        $compile_method = [];
                    }
                    $compile_method[$filename] = true;
                    $parse->data(Compile::DATA_METHOD, $compile_method);                                        
                    try {
                        //required for tag_close
                        Parse::require($parse, $filename, Parse::TYPE_FUNCTION);     
                    } catch(Exception $e){
                        return $e;
                    }                                                
                    $tag_close = strtoupper(str_replace('.', '_', $record['method']['name']) . '_TAG_CLOSE');
                    // var_dump($tag_close);
                    $has_tag_close = false;
                    if(defined('Priya\Module\Parse\\' . $tag_close)){
                        $has_tag_close = constant('Priya\Module\Parse\\' . $tag_close);
                    }
                    if(
                        $has_tag_close === true && 
                        !isset($record['token']['tag_close_nr'])
                    ){
                        $source = $parse->data('priya.parse.read.url');
                        if($source === null){
                            return new Exception('Function: ' .  $record['method']['name'] . ' needs a close tag on line: ' . $record['row'] . ' column: ' . $record['column']);
                        } else {
                            return new Exception('Function: ' .  $record['method']['name'] . ' needs a close tag on line: ' . $record['row'] . ' column: ' . $record['column'] . ' in: ' . $source);
                        }                    
                    }
                    $parameter_depth = strtoupper(str_replace('.', '_', $record['method']['name']) . '_PARAMETER_DEPTH');
                    if(defined('Priya\Module\Parse\\' . $parameter_depth)){
                        $parameter_depth = constant('Priya\Module\Parse\\' . $parameter_depth);
                    } else {
                        $parameter_depth = 1;
                    }                
                    if($has_tag_close === true){
                        $parameter = [];
                        if($parameter_depth == 1){                        
                            if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                                $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                                $parameter = $parameter_method($parse, $record);    
                                // var_dump($parameter);                                                
                            } else {
                                if(empty($record['method']['parameter'])){
                                    $parameter = null;
                                } else {                                
                                    $parameter = Compile::method_parameter($parse, $record);                                                           
                                }                            
                            }
                        } else {
                            if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){                            
                                $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                                $parameter = $parameter_method($parse, $record, $data_method);                                                
                            } else {
                                return new Exception('Not implemented yet...');
                            }                        
                        }         
                        if($parameter === null){
                            $data_method[] = Compile::string_tab($indent) . $function_name . '($parse){';                                      
                        } else {
                            if($function_name == 'function_for_each'){
                                $function_name = 'foreach';                        
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                                              
                                $extract = Compile::foreach_parameter_extract($parse, $record); //should be moved to phpc                        
                                if(
                                    empty($extract['key']) && 
                                    empty($extract['value'])
                                ){
                                    $source = $parse->data('priya.parse.read.url');
                                    if($source === null){
                                        throw new Exception('Compile error: for.each needs an "as $value" declaration or "as $key => $value" declaration on line: ' . $record['row']  . ' column: ' . $record['column']);
                                    } else {
                                        throw new Exception('Compile error: for.each needs an "as $value" declaration or "as $key => $value" declaration on line: ' . $record['row']  . ' column: ' . $record['column'] . ' in: ' . $source);
                                    }                            
                                }
                                elseif(empty($extract['key'])){
                                    $data_method[] = Compile::string_tab($indent + 1) . '$parse->data(\'' . $extract['value']['name'] .'\', ' . $extract['value']['variable'] .');';
                                } else {
                                    $data_method[] = Compile::string_tab($indent + 1) . '$parse->data(\'' . $extract['key']['name'] .'\', ' . $extract['key']['variable'] .');';
                                    $data_method[] = Compile::string_tab($indent + 1) . '$parse->data(\'' . $extract['value']['name'] .'\', ' . $extract['value']['variable'] .');';                            
                                }
                                $content = Compile::method_content($parse, $record, $data);
                                $indent = $parse->data(Compile::DATA_INDENT);
                                $parse->data(Compile::DATA_INDENT, $indent + 1);
    
                                $conversion = PHPC::conversion_data($parse, [ $content], $token, $method);
    
                                foreach($conversion as $value){
                                    $data_method[] = $value;
                                }                           
                                $parse->data(Compile::DATA_INDENT, $indent);
                                $data_method[] = Compile::string_tab($indent) . '}';                  
                            }                        
                            elseif($function_name == 'function_for'){
                                $function_name = 'for';
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                                
                                $content = Compile::method_content($parse, $record, $data);                                
                                $indent = $parse->data(Compile::DATA_INDENT);
                                $parse->data(Compile::DATA_INDENT, $indent + 1);                                                                    
                                $conversion = PHPC::conversion_data($parse, [ $content ], $token, $record);
    
                                foreach($conversion as $value){
                                    $data_method[] = $value;
                                }                           
                                $parse->data(Compile::DATA_INDENT, $indent);
                                $data_method[] = Compile::string_tab($indent) . '}';        
                            }
                            elseif($function_name == 'function_if'){
                                $function_name = 'if';
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                  
                                $data_method[] = Compile::string_tab($indent) . '}';        
                            }
                            elseif($function_name == 'function_while'){
                                $function_name = 'while';
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                  
                                $data_method[] = Compile::string_tab($indent) . '}';        
                            }
                            elseif($function_name == 'function_switch'){
                                $function_name = 'switch';
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                  
                                $data_method[] = Compile::string_tab($indent) . '}';        
                            } else {
                                $data_method[] = Compile::string_tab($indent) . $function_name . '(' . $parameter . '){';                  
                                $data_method[] = Compile::string_tab($indent) . '}';                  
                            }                                                
                        }                                                   
                    } else{
                        $parameter = [];                    
                        if($parameter_depth == 1){                        
                            if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                                $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                                $parameter = $parameter_method($parse, $record);                            
                            } else {
                                if(empty($record['method']['parameter'])){
                                    $parameter = null;
                                } else {                                    
                                    $parameter = Compile::method_parameter($parse, $record);                                                           
                                }                            
                            }
                        } else {
                            if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                                $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                                $parameter = $parameter_method($parse, $record);                            
                            } else {
                                return new Exception('Not implemented yet...');
                            }                        
                        }    
                        if($parameter === null){
                            $data_method[] = Compile::string_tab($indent) . $function_name . '($parse);';                                      
                        } else {
                            $data_method[] = Compile::string_tab($indent) . $function_name . '($parse, [' . $parameter . ']);';                                      
                        }                                                    
                    }     
                }
                elseif(!empty($record['is_executed'])){ 	
                    if($record['type'] == Token::TYPE_STRING){
                        $collect .= $record['value'];
                        // $data_method[] = Compile::string_tab(2) . '$data[] = ' . $record['value'] . ';';  
                    } else {
                        $collect .= $record['execute'];
                        // $data_method[] = Compile::string_tab(2) . '$data[] = ' . $record['execute'] . ';';  
                    }                
                }
                elseif($record['type'] == Token::TYPE_STRING){ 	  
                    $collect .= $record['value'];              
                    // $data_method[] = Compile::string_tab(2) . '$data[] = \'' . $record['value'] . '\';';           
                }
                elseif($record['type'] == Token::TYPE_WHITESPACE){ 	   
                    if($is_assign === true){
                        $explode = explode("\n" , $record['value'], 2);
                        if(isset($explode[1])){
                            $record['value'] = $explode[1];
                        }
                        $is_assign = false;
                    }
                    $collect .= $record['value'];
                    // $data_method[] = Compile::string_tab(2) . '$data[] = \'' . $record['value'] . '\';';           
                }
            }
        }
        
        if($collect !== ''){
            $data_method[] = Compile::string_tab($indent) . 'function_echo($parse, [\'' . $collect . '\']);';  
            $collect = '';
        }
        return $data_method;
    
    }

    public static function create_static_public_data(Parse $parse, $parameter=[], &$token=[], $method=[]){
        $data = key_exists(0, $parameter) ? $parameter[0] : [];      
        $result = [];
        $indent = $parse->data(Compile::DATA_INDENT);
        $result[] = Compile::string_tab($indent) . 'public static function data(Parse $parse, $attribute=null, $value=null, $type=null){';       
        $result[] = Compile::string_tab($indent + 1) . '';
        $result[] = Compile::string_tab($indent) . '}';        
        return implode(PHP_EOL, $result);        
    }

    public static function data_set(Parse $parse, $parameter=[], &$token=[], $method=[]){
        $attribute = key_exists(0, $parameter) ? $parameter[0] : null;      
        $value = key_exists(1, $parameter) ? $parameter[1] : null;      
        $result = [];
        $indent = $parse->data(Compile::DATA_INDENT);

        // $parse->data('set', 'a.b.c.d', null);        

        $has = false;
        if($attribute !== null){
            $has = $parse->data('has', $attribute);            
        }
        if($has === false){
            $value = Compile::value_to_string($parse, $value);
            // $result[] = Compile::string_tab($indent) . $parse->data('phpc.class') . '::data_set_' . $attribute . '($parse, );';
            $result[] = Compile::string_tab($indent) . '$parse->data(\'set\', \'' . $attribute . '\' , ' . $value . ');';
        }
        return implode(PHP_EOL, $result);       
    }

    public static function variable_init(Parse $parse, $parameter = [], $token=[], $record=[]){
        $attribute = key_exists(0, $parameter) ? $parameter[0] : null;      
        $data = key_exists(1, $parameter) ? $parameter[1] : null;    
        $indent = $parse->data(Compile::DATA_INDENT);
        $data_property = $parse->data(PHPC::DATA_PROPERTY);
        if($data_property === null){
            $data_property = [];
        }
        $result = [];
        if(substr($attribute, 0, 1) == '$'){
            $attribute = substr($attribute, 1);
        }
        $attributeList = Parse::explode_multi(Parse::ATTRIBUTE_EXPLODE, $attribute);
        $end = array_pop($attributeList);
        $object = '$data';        
        foreach($attributeList as $nr => $part){
            $property = $object . '->' . $part;
            if(in_array($property, $data_property)){
                $key = $part;          
                $object .= '->' . $key;                 
            } else {
                $key = $part;            
                $result[] = Compile::string_tab($indent) . 'if(!property_exists(' . $object .', \'' . $key . '\')){';
                $result[] = Compile::string_tab($indent + 1) . '//create property with stdclass';
                $object .= '->' . $key;                
                if(!in_array($object, $data_property)){
                    $data_property[] = $object;
                    $parse->data(PHPC::DATA_PROPERTY, $data_property);
                }            
                $result[] = Compile::string_tab($indent + 1) . $object . ' = ' . 'new stdClass();';
                $result[] = Compile::string_tab($indent) . '}';            
            }
            
        }
        if($end !== null){
            $key = $end;          
            $property = $object . '->' . $end;
            if(in_array($property, $data_property)){                
                $object .= '->' . $key;                 
            } else {                
                $result[] = Compile::string_tab($indent) . 'if(!property_exists(' . $object .', \'' . $key . '\')){';
                $result[] = Compile::string_tab($indent + 1) . '//create property with null';
                $result[] = Compile::string_tab($indent + 1) . $object . '->' . $key . ' = ' . 'null;';
                $result[] = Compile::string_tab($indent) . '}';
            }            
        }
        return implode(PHP_EOL, $result);       
    }

    public static function variable_value(Parse $parse, $parameter = [], $token=[], $record=[]){
        $attribute = key_exists(0, $parameter) ? $parameter[0] : null;      
        $data = key_exists(1, $parameter) ? $parameter[1] : null;    
        $value = '';
        if(substr($attribute, 0, 1) == '$'){
            $attribute = substr($attribute, 1);
        }
        if(empty($record['variable'])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            var_dump($record);
            die;
        }
        if(empty($record['variable']['value'])){
            if(
                isset($record['variable']['operator']) &&
                in_array(
                    $record['variable']['operator'],
                    [
                        '++',
                        '--',
                        '**',
                        '%%'
                    ]
                )
            ){                                
                if($attribute !== null){
                    switch($record['variable']['operator']){                        
                        case '++' :
                            $key = Compile::variable_assign_key($parse, $attribute, $data, $is_assign);
                            if($is_assign === true){
                                var_dump('undefined, is_assign in variable_assign_key');
                                die;
                            } else {
                                $value = $key . ' + 1';// . '++';
                            }             
                        break;
                        case '--' :
                            $key = Compile::variable_assign_key($parse, $attribute, $data, $is_assign);
                            if($is_assign === true){
                                var_dump('undefined, is_assign in variable_assign_key');
                                die;
                            } else {
                                $value = $key . ' - 1';// . '++';
                            }                                         
                        break;
                        /*
                        case '**' :
                            $value = '$parse->data(\'' . $attribute . '\') * 2';                        
                        break;
                        case '%%' :
                            $value = '$parse->data(\'' . $attribute . '\') %';                        
                        break;
                        */                        
                    }
                }                                
            } else {
                if($attribute !== null){
                    $value  = PHPC::conversion_variable($parse, [ $attribute, &$data ], $token, $record);                    
                    // $value  = '$parse->data(\'' . $attribute . '\')';                    
                }
            }
        } else {
            $is_plus = false;
            $plus = '';     
            var_dump($record);
            die;       
            foreach($record['variable']['value'] as $value_nr => $value_value){      
                //variable assign / no assign to...                  
                if(!empty($value_value['is_executed'])){
                    if($value_value['type'] == Token::TYPE_STRING){
                        if($is_plus === false){
                            $value .= $value_value['value'];
                        } else {
                            $plus .= $value_value['value'];
                            $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';                                    
                            $is_plus = false;
                            $plus = '';                                    
                        }                                
                    } else {
                        if($is_plus === false){
                            $value .= $value_value['execute'];
                        } else {
                            $plus .= $value_value['execute'];
                            var_dump($plus);
                            var_dump($value);
                            die;
                        }
                    }                        
                }
                elseif(
                    in_array(
                        $value_value['type'], 
                        [
                            Token::TYPE_METHOD,                                        
                        ]
                    ) 
                ){
                    if($is_plus === false){
                        //move to phpc
                        $value = Compile::method_value($parse, $token, $value_value);
                    } else {
                        $plus = Compile::method_value($parse, $token, $value_value);                                
                    }                            
                }
                elseif($value_value['is_operator'] === true){
                    if($is_plus === true){
                        $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';             
                        $is_plus = false;
                        $plus = '';                                                                       
                    }
                    if($value_value['value'] == '+'){
                        $is_plus = true;                                
                        $plus = '';
                    } else {
                        $value .= $value_value['value'];                                
                    }                                                                                    
                }
                elseif($value_value['type'] == Token::TYPE_WHITESPACE){
                    if($is_plus === false){
                        $value .= $value_value['value'];
                    } else {
                        $plus .= $value_value['value'];
                    }
                    
                }
                elseif($value_value['type'] == Token::TYPE_STRING){
                    if($is_plus === false){
                        $value .= '"' . $value_value['value'] . '"';
                    } else {
                        $plus .= '"' . $value_value['value'] . '"';
                    }
                }
                elseif(
                    $value_value['type'] == Token::TYPE_VARIABLE && 
                    empty($value_value['variable']['is_assign']) && 
                    empty($value_value['variable']['has_modifier'])
                ){                    
                    if($is_plus === false){
                        $value .= PHPC::conversion_variable($parse, [ $value_value['value'], &$data ], $token, $value_value);                        
                        // $value .= $value_value['value'];
                    } else {
                        $plus .= PHPC::conversion_variable($parse, [ $value_value['value'], &$data ], $token, $value_value);
                        // $plus .= $value_value['value'];
                    }
                }
                else {
                    var_dump($record);
                    var_Dump($value_value);
                    die;
                }                    
            }
        }                
        if($value == 'null'){
            $value = null;
        }
        elseif($value == 'true'){
            $value = true;
        }
        elseif($value == 'false'){
            $value = false;
        }
        elseif(is_numeric($value)){
            $value += 0;
        } else {
            // $value = '\''. $value . '\'';
        }
        return $value;
    }

    public static function poep(){
        $variable_assign = $parse->data(Compile::DATA_VARIABLE_ASSIGN);
                    if($variable_assign === null){
                        $variable_assign = [];
                    }
                    $attributeList = Parse::explode_multi(Parse::ATTRIBUTE_EXPLODE, $attribute);                                                                                                   
                    $key = '$data->';
                    $end = array_pop($attributeList);
                    if(!in_array($attribute, $variable_assign)){
                        $variable_assign[] = $attribute;
                        $parse->data(Compile::DATA_VARIABLE_ASSIGN, $variable_assign);         
                        foreach($attributeList as $attribute_nr => $attribute_part){
                            $key .= $attribute_part;                        
                            $data[] = Compile::string_tab($indent) . 'if(!isset(' . $key .') || !is_object(' . $key . ')){';
                            $data[] = Compile::string_tab($indent + 1) . $key . ' = new stdClass();';
                            $data[] = Compile::string_tab($indent) . '}';
                            $key .= '->';
                        }                                        
                        $key .= $end;    
                        $data[] = Compile::string_tab($indent) . 'if(!isset(' . $key . ')){';
                        $data[] = Compile::string_tab($indent + 1) . $key . ' = null;';
                        $data[] = Compile::string_tab($indent) . '}';      
                    } else {
                        foreach($attributeList as $attribute_nr => $attribute_part){
                            $key .= $attribute_part;                                                        
                            $key .= '->';
                        }                                        
                        $key .= $end;    
                    }                                            
    }
}