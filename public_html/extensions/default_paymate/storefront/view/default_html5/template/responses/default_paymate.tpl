<form action="<?php echo str_replace('&', '&amp;', $action); ?>" method="get" id="checkout">
  <input type="hidden" name="mid" value="<?php echo $mid; ?>" />
  <input type="hidden" name="amt" value="<?php echo $amt; ?>" />
  <input type="hidden" name="amt_editable" value="N" />
  <input type="hidden" name="popup" value="false" />
  <input type="hidden" name="currency" value="<?php echo $currency; ?>" />
  <input type="hidden" name="ref" value="<?php echo $ref; ?>" />
  <input type="hidden" name="pmt_sender_email" value="<?php echo $pmt_sender_email; ?>" />
  <input type="hidden" name="pmt_contact_firstname" value="<?php echo $pmt_contact_firstname; ?>" />
  <input type="hidden" name="pmt_contact_surname" value="<?php echo $pmt_contact_surname; ?>" />
  <input type="hidden" name="pmt_contact_phone" value="<?php echo $pmt_contact_phone; ?>" />
  <input type="hidden" name="pmt_country" value="<?php echo $pmt_country; ?>" />
  <input type="hidden" name="regindi_address1" value="<?php echo $regindi_address1; ?>" />
  <input type="hidden" name="regindi_address2" value="<?php echo $regindi_address2; ?>" />
  <input type="hidden" name="regindi_sub" value="<?php echo $regindi_sub; ?>" />
  <input type="hidden" name="regindi_state" value="<?php echo $regindi_state; ?>" />
  <input type="hidden" name="regindi_pcode" value="<?php echo $regindi_pcode; ?>" />
  <input type="hidden" name="return" value="<?php echo $return; ?>" />
  
	<div class="control-group">
	   <div class="controls">
	   	<button class="btn btn-orange pull-right" title="<?php echo $button_confirm; ?>" onclick="$('#checkout').submit();" type="submit">
	   	    <i class="icon-ok icon-white"></i>
	   	    <?php echo $button_confirm; ?>
	   	</button>
	   	<a  href="<?php echo str_replace('&', '&amp;', $back); ?>" class="btn mr10" title="<?php echo $button_back; ?>">
	   	    <i class="icon-arrow-left"></i>
	   	    <?php echo $button_back; ?>
	   	</a>
	    </div>
	</div>
    
</form>