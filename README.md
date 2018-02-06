# Dynamic-Blocks
Plugin Wordpress

Preview: https://i.imgur.com/juyGDTO.png

Examples:

Shortcode:
```html
[dynamic_blocks root-class='col-lg-3' template='<div class="dynamic-blocks-single"><h2>{title}</h2><br>{content}</div>']
```
Function PHP for template (array):
```php
if(function_exists('get_dynamic_blocks')) var_dump(get_dynamic_blocks());
```
