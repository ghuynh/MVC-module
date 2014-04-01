<?php
	$this->form->prefix = 'enquire';
	$this->form->data= $this->data;
	$this->form->error = $this->error;
	$e = array();
	$this->mergeErrorMsg($this->error, $e);
?>
<div>
<h2 class="title">Contact Us</h2>

<?php if (!empty($this->message)) {?>
<div class="message">
<?php echo $this->message;?>
</div>
<?php }?>

<?php echo $this->renderModule('custom', 'in enquire form');?>

<?php if($e){
	      echo "Would you please" ;
      }
?>
<div class="errormsg">
<?php  foreach($e as $msg) {?>
<p><?php echo $msg ?></p>
<?php } ?>
</div>

<form method="post" action="#">
<table cellpadding="0" cellspacing="0" class="querytable">
	<tr>
		<td colspan="2"><label>Comment:</label><br />
			<?php echo $this->form->textarea('comment', array('rows'=>6, 'cols'=>200))?>
		</td>
	</tr>
	<tr>
		<td><label>First name</label><br />
			<?php echo $this->form->text('firstname', array('class'=>'text')); ?>
		</td>
		<td><label>Last name</label><br />
			<?php echo $this->form->text('lastname', array('class'=>'text'));?>
		</td>
	</tr>

	<tr>
		<td><label>Email </label> <br />
			<?php echo $this->form->text('email', array('class'=>'text'));?>
		</td>
		<td><label>Preferred contact number </label><br />
			<?php echo $this->form->text('phone', array('class'=>'text'))?>
		</td>
	</tr>
	<tr>
		<td>
		<?php echo $this->form->image('submitButton',
				array('src'=>"/assets/images/send.gif",
					'alt'=>"send",		
					'value'=>'submit'
				))?>
		</td>
		<td></td>
	</tr>
</table>
<?php echo $this->form->hidden('type', array('default'=>'general'))?>
<?php echo JHTML::_('form.token');?>
<input type="hidden" name="enquire[submit]" value="1" />
</form>
</div>
<script type="text/javascript">
$("div.coursedetail").addClass('standalone');
</script>