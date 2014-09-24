<form action="<?php echo admin_url( 'admin.php?page=myLCO_edit' ); ?>&amp;cl={cl}" name="edit" method="post">
<input type="hidden" name="action" value=""/>
<table class="widefat">
<thead>
<tr>
<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></th>
<th scope="col"><?php _e ('Backlink URL', _MYLCO_); ?></th>
<th scope="col"><?php _e ('PageRank', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Link text', _MYLCO_); ?></th>
<th scope="col"><?php _e ('IP', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Status', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Last checkdate', _MYLCO_); ?></th>
</tr>
</thead>
<tfoot>
<tr>
<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
<th scope="col"><?php _e ('Backlink URL', _MYLCO_); ?></th>
<th scope="col"><?php _e ('PageRank', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Link text', _MYLCO_); ?></th>
<th scope="col"><?php _e ('IP', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Status', _MYLCO_); ?></th>
<th scope="col"><?php _e ('Last checkdate', _MYLCO_); ?></th>
</tr>
</tfoot>
<tbody>
{content}
</tbody>
</table>
<p class="submit">
<input value="<?php _e ('Check selected URLs', _MYLCO_); ?>" class="button-primary" type="button" onclick="document.edit.action.value = 'check'; document.edit.submit ();"/>
<input value="<?php _e ('Delete selected URLs', _MYLCO_); ?>" class="button-secondary" type="button" onclick="if (confirm ('<?php _e ('Are you sure you want to delete the selected URLs? Please click on OK to continue, or CANCEL if you are not sure!', _MYLCO_); ?>')) { document.edit.action.value = 'delete'; document.edit.submit (); } return false;"/>
</p>
</form>
<div class="tablenav"><span class="displaying-num">{tablenav}</span></div>
