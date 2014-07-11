<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=myLCO_edit" method="post">
<div class="tablenav">
<select name="cl">
{options}
</select>
<input name="change" value="<?php _e ('Change selection', 'myLCO'); ?>" class="button-secondary" type="submit"/>
<input class="regular-text" type="text" name="backlink" value=""/>
<input name="insert" value="<?php _e ('Insert backlink', 'myLCO'); ?>" class="button-secondary" type="submit"/>
</div>
</form>