<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

const FOR_TAG_CLOSE = true;
const FOR_PARAMETER_DEPTH = 2;

function function_for(Parse $parse, $method=[], $token=[], $keep=false){


    var_dump($method);
    die;

    if(!isset($method['method'])){
        return $token;
    }
    if($method['method']['name'] != 'for'){
        return $token;
    }    
    $method = Core_for::select($method, $token, true);
    $token = Core_for::execute($parse, $method, $token, $keep, true);
    return $token;
}

function function_for_parameter(Parse $parse, $record=[], &$data=[]){
    $parameter = [];    
    $parse->data(Compile::DATA_FOR, true);
    $indent = $parse->data(Compile::DATA_INDENT);          
    foreach($record['method']['parameter'] as $parameter_nr => $set){
        foreach($set as $set_nr => $parameter_token){        
            // $parameter[$parameter_nr][$set_nr] = '';
            $is_plus = false;
            $plus = '';
            $value = '';
            foreach($parameter_token as $parameter_token_nr => $parameter_token_value){       
                if(!empty($parameter_token_value['is_executed'])){
                    if($parameter_token_value['type'] == Token::TYPE_STRING){
                        if($is_plus === false){
                            $value .= $parameter_token_value['value'];
                        } else {
                            var_Dump(';fu');
                            die;
                            $plus .= $parameter_token_value['value'];
                            $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';                                    
                            $is_plus = false;
                            $plus = '';                                    
                        }                                
                    } else {
                        if($is_plus === false){
                            $value .= $parameter_token_value['execute'];
                        } else {
                            $plus .= $parameter_token_value['execute'];
                            var_dump($plus);
                            var_dump($value);
                            die;
                        }
                    }                        
                }            
                elseif(
                    $parameter_token_value['type'] == Token::TYPE_VARIABLE &&
                    $parameter_token_value['variable']['is_assign'] === true
                ){
                    $attribute = substr($parameter_token_value['variable']['name'], 1);                 
                    $variable_value = PHPC::variable_value($parse, [ $attribute, &$data ], $parameter_token, $parameter_token_value);                    
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
                        $data[] = PHPC::data_set($parse, [ $attribute, null ], $token, $record);
                        /*   
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
                    */
                    }  
                    foreach($attributeList as $attribute_nr => $attribute_part){
                        $key .= $attribute_part;                                                        
                        $key .= '->';
                    }                                        
                    $key .= $end;                                         
                    if(is_string($variable_value)){
                        if(substr($variable_value, 0, 1) == '$'){
                            $value .= $key . ' = ' . $variable_value;
                            // VAR_DUMP($value);
                            // die;
                        } else {
                            $explode = explode('::', $variable_value, 2);
                            if(isset($explode[1])){
                                $left = substr($variable_value, 0, strlen($explode[0]));
                                if($left == $explode[0]){                                
                                    $value .= $key  . ' = ' . $variable_value;
                                } else {
                                    $value .= $key  . ' = \'' . $variable_value . '\'';                                
                                }                                                        
                            } else {
                                $value .= $key  . ' = \'' . $variable_value . '\'';                            
                            }   
                        }
                                             
                    } else {
                        $value .= $key  . ' = ' . $variable_value;
                    }         
                }
                elseif(
                    $parameter_token_value['type'] == Token::TYPE_VARIABLE &&
                    $parameter_token_value['variable']['is_assign'] === false
                ){
                    if(empty($parameter_token_value['variable']['has_modifier'])){
                        $attribute = substr($parameter_token_value['variable']['name'], 1);                         
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
                            // $data[] = Compile::string_tab($indent) 
                            var_dump($record);
                            die;
                            $data[] = PHPC::data_set($parse, [], $token, $record);
                            

                            /*
                            foreach($attributeList as $attribute_nr => $attribute_part){
                                $key .= $attribute_part;                        
                                $data[] = Compile::string_tab($indent) . 'if(!isset(' . $key . ') || !is_object(' . $key . ')){';
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
                        */  
                        }
                        foreach($attributeList as $attribute_nr => $attribute_part){
                            $key .= $attribute_part;                                                    
                            $key .= '->';
                        }                                        
                        $key .= $end;    


                        $value .= $key;                                   
                    } else {
                        return new Exception('Not implemented yet...');
                    }
                }
                elseif($parameter_token_value['type'] == Token::TYPE_METHOD){
                    $method_value = Compile::method_value($parse, $parameter_token, $parameter_token_value);
                    $value .= $method_value;                    
                }                
                elseif($parameter_token_value['is_operator'] === true){
                    if($is_plus === true){
                        $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';             
                        $is_plus = false;
                        $plus = '';                                                                       
                    }
                    if($parameter_token_value['value'] == '+'){
                        $is_plus = true;                                
                        $plus = '';
                    } else {
                        $value .= $parameter_token_value['value'];                                
                    } 
                }
                elseif($parameter_token_value['type'] == Token::TYPE_WHITESPACE){
                    if($is_plus === false){
                        $value .= $parameter_token_value['value'];
                    } else {
                        $plus .= $parameter_token_value['value'];
                    }                    
                }
                elseif($parameter_token_value['type'] == Token::TYPE_STRING){
                    if($is_plus === false){
                        $value .= '"' . $parameter_token_value['value'] . '"';
                    } else {
                        $plus .= '"' . $parameter_token_value['value'] . '"';
                    }
                }
                else {
                    var_Dump($parameter_token_value);
                    die;               
                    return new Exception('Not implemented yet...');
                }
            }
            $parameter[$parameter_nr][$set_nr] = $value;            
        }
    }
    if(!isset($parameter[1])){
        $parameter[1][0] = 'true';        
    }
    if(!isset($parameter[2])){
        $parameter[2][0] = null;        
    }
    foreach($parameter as $nr => $set){
        if(count($set) > 1){
            $parameter[$nr] = implode(', ', $set);
        } else {
            $parameter[$nr] = reset($set);
        } 
    }
    $result = '';
    foreach($parameter as $nr => $set){
        $result .= $set . '; ';
    }
    $result = substr($result, 0, -2);      
    return $result;    
}