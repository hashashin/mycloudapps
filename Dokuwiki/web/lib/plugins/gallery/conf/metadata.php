<?php
/**
 * Options for the gallery plugin
 *
 * @author Dmitry Baikov <dsbaikov@gmail.com>
 */

$meta['thumbnail_width']  = array('numeric');
$meta['thumbnail_height'] = array('numeric');
$meta['image_width']      = array('numeric');
$meta['image_height']     = array('numeric');
$meta['cols']             = array('numeric');

$meta['sort']    = array('multichoice', '_choices' => array('file','mod','date','title'));
$meta['options'] = array('multicheckbox', '_choices' => array('cache','crop','direct','lightbox','random','reverse','showname','showtitle'));

