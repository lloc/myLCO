<tr{alternate_class}>
<th scope="row" class="check-column"><input type="checkbox" name="url[]" value="{backlink_url}"/></td>
<td>
<a href="{backlink_url}" title="<?php _e ('Visit linking page...', 'myLCO'); ?>" target="_blank">{backlink_url}</a>
<div class="row-actions">
<span class="inline"><a href="javascript:void(0);" onclick="document.getElementById('hidden_{hnum}').style.display = 'block';"><?php _e ('Details', 'myLCO'); ?></a> | </span>
<span class="edit"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=myLCO_edit&amp;cl={cl}&amp;action=check&url={backlink_url}"><?php _e ('Check', 'myLCO'); ?></a> | </span>
<span class="trash"><a class="submitdelete" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=myLCO_edit&amp;cl={cl}&amp;action=delete&url={backlink_url}" onclick="if (confirm ('{DeleteMessage}')) { return true; } return false;"><?php _e ('Delete', 'myLCO'); ?></a></span>
</div>
<div id="hidden_{hnum}" class="hidden">
<fieldset>
<p>
<label for="cn_{hnum}"><?php echo __ ('Partner', 'myLCO'); ?></label><br/>
<input class="regular-text" type="text" id="cn_{hnum}" name="contact_name[{hnum}]" value="{contact_name}"/><br/>
<label for="ce_{hnum}">{Kemail}</label><br/>
<input class="regular-text" type="text" id="ce_{hnum}" name="contact_email[{hnum}]" value="{contact_email}"/><br/>
<label for="cr_{hnum}"><?php _e ('Remarks / Backlink', 'myLCO'); ?></label><br/>
<input class="regular-text" type="text" id="cr_{hnum}" name="contact_remarks[{hnum}]" value="{contact_remarks}"/><br/>
</p>
<p class="submit">
<input value="<?php _e ('Save', 'myLCO'); ?>" class="button-secondary" type="button" onclick="document.edit.action.value = 'contact[{hnum}]'; document.edit.submit ();"/>
<input value="<?php _e ('Cancel', 'myLCO'); ?>" class="button-secondary" type="button" onclick="document.getElementById('hidden_{hnum}').style.display = 'none';"/>
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
