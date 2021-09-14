<?php

use com\cminds\payperposts\model\Labels;

$labelsByCategories = Labels::getLabelsByCategories();

foreach ($labelsByCategories as $category => $labels):

	?><table><caption><?php echo (empty($category) ? 'Other' : $category); ?></caption><?php

	foreach ($labels as $key):
		
		if ($default = Labels::getDefaultLabel($key)) :
	
			?>
		
			<tr valign="top">
		        <th scope="row" valign="middle" align="left" ><?php echo esc_html($key) ?></th>
		        <td ><input type="text" size="60" name="label_<?php echo esc_attr($key); ?>"
		        	value="<?php echo esc_attr(Labels::getLabel($key)); ?>"
		        	placeholder="<?php echo esc_attr($default) ?>"/></td>
		        <td><?php echo Labels::getDescription($key); ?></td>
		    </tr>
		    
	    <?php endif; ?>
	<?php endforeach; ?>
	</table>
<?php endforeach; ?>