<?php
/**
 * @var HtmlRendering $rendering
 * @var HttpController $controller
 * @var HttpRequest $request
 * @var HttpRoute $route
 *
 * @var FormToken $formToken
 */

use App\Entity\User;
use Orpheus\Form\FormToken;
use Orpheus\InputController\HttpController\HttpController;
use Orpheus\InputController\HttpController\HttpRequest;
use Orpheus\InputController\HttpController\HttpRoute;
use Orpheus\Rendering\HtmlRendering;

$rendering->useLayout('layout.public-contents');

?>
<div class="row">
	
	<div class="col-6">
		<form method="POST" role="form"><?php echo $formToken; ?>
			<fieldset>
				<legend>Sign in</legend>
				<p class="legend"><?php echo t('login.legend', DOMAIN_APP); ?></p>
				<?php
				$this->display('reports');
				?>
				<div class="mb-3">
					<label for="loginEmail"><?php echo User::text('email'); ?></label>
					<input type="text" class="form-control" id="loginEmail" name="login[email]" placeholder="Enter your email"/>
				</div>
				<div class="mb-3">
					<label for="loginPassword"><?php echo User::text('password'); ?></label>
					<input type="password" class="form-control" id="loginPassword" name="login[password]" placeholder="Enter your password"/>
				</div>
				
				<div class="text-end">
					<button name="submitLogin" type="submit" class="btn btn-primary">Sign in</button>
				</div>
			</fieldset>
		</form>
	</div>
	
	<div class="col-6">
		<form method="POST" role="form"><?php echo $formToken; ?>
			<fieldset>
				<legend>Register</legend>
				<p class="legend"><?php echo t('register.legend', DOMAIN_APP); ?></p>
				<?php
				$this->display('reports', ['reportStream' => 'register']);
				?>
				
				<div class="mb-3">
					<label for="registerFullname"><?php echo User::text('yourName'); ?></label>
					<input type="text" class="form-control" id="registerFullname" name="user[fullname]" required placeholder="My displayed name, e.g. John Smith"/>
				</div>
				<div class="mb-3">
					<label for="registerEmail"><?php echo User::text('email'); ?></label>
					<input type="email" class="form-control" id="registerEmail" name="user[email]" required placeholder="My email, e.g. john.smith@domain.com"/>
				</div>
				<div class="mb-3">
					<label for="registerPassword"><?php echo User::text('password'); ?></label>
					<input type="password" class="form-control" id="registerPassword" name="user[password]" required/>
				</div>
				<div class="mb-3">
					<label for="registerConfirmPassword"><?php echo User::text('confirmPassword'); ?></label>
					<input type="password" class="form-control" id="registerConfirmPassword" name="user[password_conf]" required/>
				</div>
				
				<p class="mt-3 small"><?php echo html(t('register.gdprDisclaimer', DOMAIN_APP)); ?></p>
				
				<div class="text-end">
					<button name="submitRegister" type="submit" class="btn btn-primary">Register</button>
				</div>
			</fieldset>
		</form>
	</div>
</div>
