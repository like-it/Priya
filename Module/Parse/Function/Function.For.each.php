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

const FOR_EACH_TAG_CLOSE = true;

function function_for_each(Parse $parse, $method=[], $token=[], $keep=false){

    die('happyt end');
    
    if(!isset($method['method'])){
        return $token;
    }
    if($method['method']['name'] != 'for.each'){
        return $token;
    }
    // $method = Core_foreach::select($method, $token, true);
    // $token = Core_foreach::execute($parse, $method, $token, $keep, true);
    // return $token;
}

function function_for_each_parameter(Parse $parse, $record=[]){
    $parameter = [];
    foreach($record['method']['parameter'] as $parameter_nr => $parameter_token){        
        $parameter[$parameter_nr] = '';
        $is_source = false;
        $is_as = false;
        $is_attribute = false;
        $is_array_operator = false;
        foreach($parameter_token as $parameter_token_nr => $parameter_token_value){                                
            if(
                $is_source === false &&
                $parameter_token_value['type'] == Token::TYPE_VARIABLE &&
                empty($parameter_token_value['variable']['is_assign'])                                    
            ){
                $is_source = true;
                if(empty($parameter_token_value['variable']['has_modifier'])){
                    $attribute = substr($parameter_token_value['variable']['name'], 1);                                            
                    $parameter[$parameter_nr] .= '$parse->data(\'' . $attribute . '\')';                                            
                } else {
                    return new Exception('Not implemented yet...');
                }
            }                                                            
            elseif(
                $is_source !== null && 
                $parameter_token_value['type'] == Token::TYPE_STRING &&                                      
                empty($parameter_token_value['is_executed']) &&
                strtolower($parameter_token_value['value']) == 'as'                                      
            ){
                $parameter[$parameter_nr] .= 'as';                                          
                $is_as = true;                                                                        
            }
            elseif($parameter_token_value['type'] == Token::TYPE_WHITESPACE){
                $parameter[$parameter_nr] .= ' ';
                continue;   
            }
            elseif($is_as === true){
                if($parameter_token_value['type'] == Token::TYPE_VARIABLE){
                    if($parameter_token_value['variable']['is_assign'] === true){
                        return new Exception('Not implemented yet...');
                    }
                    elseif(!empty($parameter_token_value['variable']['has_modifier'])){
                        return new Exception('Not implemented yet...');
                    }
                    else {                                            
                        //need to rename these to $parse                                                                          
                        $key = strtolower(str_replace('.', '_', $parameter_token_value['variable']['name']));                                            
                        $parameter[$parameter_nr] .= $key;  
                        $is_attribute = true;                                                                                    
                    }
                } else {
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        return new Exception('For.each: as parameter needs to be a variable on line: '. $parameter_token_value['row'] . ' column: ' . $parameter_token_value['column']);
                    } else {
                        return new Exception('For.each: as parameter needs to be a variable on line: '. $parameter_token_value['row'] . ' column: ' . $parameter_token_value['column'] . ' file: ' . $source);
                    }                                                                                
                }                                    
                $is_as = false;
            }  
            elseif(
                $is_attribute === true && 
                $is_array_operator === false
            ){
                if($parameter_token_value['type'] == Token::TYPE_IS_ARRAY_OPERATOR){
                    $parameter[$parameter_nr] .= '=>';
                    $is_array_operator = true;
                }             
            }    
            elseif($is_array_operator === true){
                if($parameter_token_value['type'] == Token::TYPE_VARIABLE){
                    if($parameter_token_value['variable']['is_assign'] === true){
                        return new Exception('Not implemented yet...');
                    }
                    elseif(!empty($parameter_token_value['variable']['has_modifier'])){
                        return new Exception('Not implemented yet...');
                    } else {                                            
                        //need to rename these to $parse                                                                          
                        $value = strtolower(str_replace('.', '_', $parameter_token_value['variable']['name']));                                            
                        $parameter[$parameter_nr] .= $value;  
                        $is_value = true;                                                                                    
                    }
                } else {
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        return new Exception('For.each: as parameter needs to be a variable on line: '. $parameter_token_value['row'] . ' column: ' . $parameter_token_value['column']);
                    } else {
                        return new Exception('For.each: as parameter needs to be a variable on line: '. $parameter_token_value['row'] . ' column: ' . $parameter_token_value['column'] . ' file: ' . $source);
                    }                                                                                
                }                                                  
            }
            else {
                var_dump($parameter_token_value);
                die;
            }
        }
    }    
    $parameter = implode(',', $parameter);
    return $parameter;
}