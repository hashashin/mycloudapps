<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <gohr@cosmocode.de>
 *
 * Simple redirector script to avoid security warnings when embedding HTTP in SSL secured sites
 *
 * To avoid open redirects, a secret hash has to be provided
 */
if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__) . '/../../../');
define('NOSESSION', true);
require_once(DOKU_INC . 'inc/init.php');
global $INPUT;

$url  = $INPUT->str('url');
$hash = $INPUT->str('hash');

if(!$url) die('sorry. no url');
if(!$hash) die('sorry. no hash');
if($hash != md5(auth_cookiesalt() . 'vshare' . $url)) die('sorry. wrong hash');

send_redirect($url);