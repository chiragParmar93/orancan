<?php echo $template['partials']['alerts']; ?>
<?php echo form_open('authe/reset-password') ?>

<h5>New Password</h5>
<p><input type="password" name="password"> </p>

<h5>Confirm Password</h5>
<p><input type="password" name="confirm-password"> </p>

<p>
	<input type="submit">
</p>

</form>