<div class="cd-user-modal"> <!-- this is the entire modal form, including the background -->
		<div class="cd-user-modal-container"> <!-- this is the container wrapper -->
			<ul class="cd-switcher">
				<li><a href="#0">Sign in</a></li>
				<li><a href="#0">New account</a></li>
			</ul>

			<div id="cd-login"> <!-- log in form -->
				<?php echo form_open('authe/login_ajax_post', 'id="form-login"'); ?>
								<form>
									<div class="form-group">
										<label>Email</label>
										<input type="email" class="form-control" name="email" placeholder="Email" data-validation="email" >
									</div>

									<div class="form-group">
										<label>Password</label>
										<input type="password" class="form-control" name="password" placeholder="********"
											data-validation="required" >
									</div>
									
									<p class="clearfix" style="margin-top:-15px;">
										<a href="#tab-form-forgot-password" role="tab" data-toggle="tab" class="pull-right">Forgot Password?</a>
									</p>

									<input type="submit" class="btn btn-purple-sm center-block" value="Sign In" data-loading-text="Processing...">
								</form>
				
				<p class="cd-form-bottom-message"><a href="#0">Forgot your password?</a></p>
				<!-- <a href="#0" class="cd-close-form">Close</a> -->
			</div> <!-- cd-login -->

			<div id="cd-signup"> <!-- sign up form -->
				<?php echo form_open('authe/register-ajax-post', 'id="form-register"'); ?>
							
							<div class="form-group">
								<label>First Name</label>
								<input type="text" class="form-control" name="first_name"  placeholder="Your Name" 
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Last Name</label>
								<input type="text" class="form-control" name="last_name"  placeholder="Your Name" 
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Email</label>
								<input type="email" class="form-control" name="email" placeholder="Email" 
								data-validation="email"
								>
							</div>
							<div class="form-group">
								<label>Password</label>
								<input type="password" class="form-control" name="password" placeholder="********"
								data-validation="required"
								>
							</div>
							<div class="form-group">
								<label>Confirm Password</label>
								<input type="password" class="form-control" placeholder="********"
								data-validation-confirm="password"
								>
							</div>

							<input type="submit" class="btn btn-purple-sm center-block" value="Signup" data-loading-text="Processing...">
						</form>

				<!-- <a href="#0" class="cd-close-form">Close</a> -->
			</div> <!-- cd-signup -->

			<div id="cd-reset-password"> <!-- reset password form -->
				<p class="cd-form-message">Lost your password? Please enter your email address. You will receive a link to create a new password.</p>

				<form class="cd-form">
					<p class="fieldset">
						<label class="image-replace cd-email" for="reset-email">E-mail</label>
						<input class="full-width has-padding has-border" id="reset-email" type="email" placeholder="E-mail">
						<span class="cd-error-message">Error message here!</span>
					</p>

					<p class="fieldset">
						<input class="full-width has-padding" type="submit" value="Reset password">
					</p>
				</form>

				<p class="cd-form-bottom-message"><a href="#0">Back to log-in</a></p>
			</div> <!-- cd-reset-password -->
			<a href="#0" class="cd-close-form">Close</a>
		</div> <!-- cd-user-modal-container -->
	</div> 
<!-- below areOld Form -->
<!--<div class="modal fade" id="modal-form-login" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body">

				<div class="panel panel-form">
					<div class="panel-heading">
						<h3 class="panel-title">Welcome to Elbut Stan!</h3>
					</div>
					<div class="panel-body">

						<div class="tab-content">

							<div role="tabpanel" class="tab-pane fade in active" id="tab-form-login">
								<?php echo form_open('authe/login-ajax-post', 'id="form-login"'); ?>
								<form>
									<div class="form-group">
										<label>Email</label>
										<input type="email" class="form-control" name="email" placeholder="Email" data-validation="email" >
									</div>

									<div class="form-group">
										<label>Password</label>
										<input type="password" class="form-control" name="password" placeholder="********"
											data-validation="required" >
									</div>
									
									<p class="clearfix" style="margin-top:-15px;">
										<a href="#tab-form-forgot-password" role="tab" data-toggle="tab" class="pull-right">Forgot Password?</a>
									</p>

									<input type="submit" class="btn btn-purple-sm center-block" value="Sign In" data-loading-text="Processing...">
								</form>
							</div>

							<div role="tabpanel" class="tab-pane fade" id="tab-form-forgot-password">
								<?php echo form_open('authe/forgot-password-ajax-post', 'id="form-forgot-password"'); ?>
								<form>
									<div class="form-group">
										<label>Email</label>
										<input type="email" class="form-control" name="email" placeholder="Email" data-validation="email" >
									</div>

									<p class="clearfix" style="margin-top:-15px;">
										<a href="#tab-form-login" class="pull-right" role="tab" data-toggle="tab">Back to Login</a>
									</p>

									<input type="submit" class="btn btn-purple-sm center-block" value="Reset" data-loading-text="Processing...">
								</form>

							</div>
							
						</div>
						

						<hr>

						<div class="text-center">
							<p class="font-semibold">Login with Social Media</p>
							<a class="fb btn-social-login" href="<?= site_url('authe/login-social/Facebook')?>"></a> <br />
							<a class="tw btn-social-login" href="<?= site_url('authe/login-social/Twitter')?>"></a> <br />
							<a class="gp btn-social-login" href="<?= site_url('authe/login-social/Google')?>"></a> <br />
						</div>



					</div>  /.panel-body 

				</div>

			</div>
		</div>
	</div>
</div>-->

