<form action="<?php echo admin_url( 'admin.php?page=myLCO_options' ); ?>" method="post">
<table class="form-table">
<tbody>
<tr>
<th scope="row">
<?php _e ('myLCO link category', _MYLCO_); ?>
</th>
<td>
<input id="category_name" name="category_name" value="{category_name}"/>
</td>
</tr>
<tr>
<th scope="row">
<?php _e ('API Key (<a href="http://webinfodb.net/api">?</a>)', _MYLCO_); ?>
</th>
<td>
<input id="api_key" name="api_key" value="{api_key}"/>
</td>
</tr>
<tr>
<th scope="row">
<?php _e ('Hide private links', _MYLCO_); ?>
</th>
<td>
<input type="checkbox" name="hide_invisible" value="1"{hide_invisible}/>
</td>
</tr>
</tbody>
</table>
<p class="submit">
<input name="save" value="<?php _e ('Save options', _MYLCO_); ?>" class="button-primary" type="submit"/>
</p>
</form>
