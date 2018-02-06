# Dynamic-Blocks
Plugin Wordpress

Examples:

Shortcode: [dynamic_blocks root-class='col-lg-3' template='<div class="dynamic-blocs-single"><h2>{title}</h2><br>{content}</div>']
Array (in template): if(function_exists('get_dynamic_blocks')) var_dump(get_dynamic_blocks());
