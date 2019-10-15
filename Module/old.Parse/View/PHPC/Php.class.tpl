<?php
/**
 * @copyright	(c) 2015 - 2019 priya.software
 * @license		https://priya.software/license/
 * @author		Remco van der Velde
 * @version	    0.0.1
 * @support		https://priya.software/support/
 * @package		Priya\Module\Parse\Compile\PHPC
 * @category	View
 * @note        This is a rem template for compiling a rem file to a php class combined in the PHPC package
 */
{ $is.debug = true }
{ $module.dir.phpc = $module.dir.view + PHPC + '/' }
{ import.function( $module.dir.phpc + Function ) } 
{ echo ( phpc.header() ) }
{ $phpc.namespace = $module.namespace + '\\' + Compile }
{ $phpc.class = 'Rem_' + sha1( 'Rem/' + $priya.parse.read.url2 | default2 : 'Compile/' + phpc.parse.id()) }

namespace { $phpc.namespace };

{ $phpc.use | default2: phpc.use() }

{ $phpc.require | default2: phpc.require()}

class { $phpc.class } { literal }{
{ /literal }
{ $priya.parse.compile.indent = 1 }
{ $phpc.constant | default2 : phpc.constant() }    
{ $phpc.variable | default2: phpc.variable() }    
{ $phpc.method | default2: phpc.method() }        
{ $phpc.static | default2: phpc.static(null, $priya.parse.token) }        

{ literal }}{ /literal }