<tr{alternate_class}>
<th scope="row" class="check-column"><input type="checkbox" name="url[]" value="{backlink_url}"/></td>
<td>
<a href="{backlink_url}" title="<?php _e ('Visit linking page...', _MYLCO_); ?>" target="_blank">{backlink_url}</a>
<div class="row-actions">
<span class="inline"><a href="javascript:void(0);" onclick="document.getElementById('hidden_{hnum}').style.display = 'block';"><?php _e ('Details', _MYLCO_); ?></a> | </span>
<span class="edit"><a href="<?php echo admin_url( 'admin.php?page=myLCO_edit' ); ?>&amp;cl={cl}&amp;action=check&url={backlink_url}"><?php _e ('Check', _MYLCO_); ?></a> | </span>
<span class="trash"><a class="submitdelete" href="<?php echo admin_url( 'admin.php?page=myLCO_edit' ); ?>&amp;cl={cl}&amp;action=delete&url={backlink_url}" onclick="if (confirm ('{DeleteMessage}')) { return true; } return false;"><?php _e ('Delete', _MYLCO_); ?></a></span>
</div>
<div id="hidden_{hnum}" class="hidden">
<fieldset>
<p>
<label for="cn_{hnum}"><?php echo __ ('Partner', _MYLCO_); ?></label><br/>
<input class="regular-text" type="text" id="cn_{hnum}" name="contact_name[{hnum}]" value="{contact_name}"/><br/>
<label for="ce_{hnum}">{Kemail}</label><br/>
<input class="regular-text" type="text" id="ce_{hnum}" name="contact_email[{hnum}]" value="{contact_email}"/><br/>
<label for="cr_{hnum}"><?php _e ('Remarks / Backlink', _MYLCO_); ?></label><br/>
<input class="regular-text" type="text" id="cr_{hnum}" name="contact_remarks[{hnum}]" value="{contact_remarks}"/><br/>
</p>
<p class="submit">
<input value="<?php _e ('Save', _MYLCO_); ?>" class="button-secondary" type="button" onclick="document.edit.action.value = 'contact[{hnum}]'; document.edit.submit ();"/>
<input value="<?php _e ('Cancel', _MYLCO_); ?>" class="button-secondary" type="button" onclick="document.getElementById('hidden_{hnum}').style.display = 'none';"/>
</p>
</fieldset>
</div>
</td>
<td>{backlink_pr}</td>
<td>{backlink_text}</td>
<td style="white-space:nowrap !important;">{backlink_ip}</td>
<td>{backlink_icon}</td>
<td style="white-space:nowrap !important;">{backlink_checkdate}</td>
</tr>
