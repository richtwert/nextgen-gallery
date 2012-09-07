<?php
$nextgen_thumb_size_custom_style = null;

if (class_exists('C_Component_Registry'))
{
	$registry = C_Component_Registry::get_instance();
	$settings = $registry->get_utility('I_NextGen_Settings');

	if ($settings != null)
	{
		$thumb_sizes = $settings->thumbnail_dimensions;

		if ($thumb_sizes != null)
		{
			$size_selected = null;
			$size_select_html = '
<select name="thumbsize_select" id="thumbsize_select" onchange="' .
htmlspecialchars(str_replace(array("\r", "\n"), '', '
var jt = jQuery(this);
var szcust = jt.next(\'.nextgen-thumb-size-custom\');
if (jt.val() == \'custom\') {
	szcust.show();
}
else {
	var parts = jt.val().split(\'x\');
	szcust.hide();
	console.log(parts);
	console.log(szcust.find(\'[name="thumbwidth"]\'));
	szcust.find(\'[name="thumbwidth"]\').val(parts[0]);
	szcust.find(\'[name="thumbheight"]\').val(parts[1]);
}
	')) . '">';

			foreach ($thumb_sizes as $thumb_size)
			{
				$thumb_size_parts = explode('x', $thumb_size);
				$thumb_width = $thumb_size_parts[0];
				$thumb_height = $thumb_size_parts[1];

				$size_select_html .= '
<option value="' . $thumb_size . '"';

				if ($ngg->options['thumbwidth'] == $thumb_width && $ngg->options['thumbheight'] == $thumb_height)
				{
					$size_selected = $thumb_size;
					$size_select_html .= ' selected="selected"';
				}

				$size_select_html .= '>' . $thumb_size . '</option>';
			}

			$size_select_html .= '
<option value="custom"';

			if ($size_selected == null)
			{
				$size_select_html .= ' selected="selected"';
			}
			else
			{
				$nextgen_thumb_size_custom_style .= 'display:none;';
			}

			$size_select_html .= '>' . __('Custom', 'nggallery') . '</option>';

			$size_select_html .= '
</select>';

			echo $size_select_html;
		}
	}
}

if ($nextgen_thumb_size_custom_style != null)
{
	$nextgen_thumb_size_custom_style = ' style="' . $nextgen_thumb_size_custom_style . '"';
}
?>
<div class="nextgen-thumb-size-custom"<?php echo $nextgen_thumb_size_custom_style; ?>>
<input type="text" size="5" maxlength="5" name="thumbwidth" value="<?php echo $ngg->options['thumbwidth']; ?>" /> x <input type="text" size="5" maxlength="5" name="thumbheight" value="<?php echo $ngg->options['thumbheight']; ?>" />
<br /><small><?php _e('These are maximum values ','nggallery') ?></small>
</div>
