<div class="container">
	<!-- <a href="<?=base_url()?>">back</a> --><h1>Login</h1>
	<form action="<?=base_url()?>app/login" method="post">
		<input name="username" placeholder="username"><br>
		<input name="password" type="password" placeholder="password"><br>

		<button type="submit">Login</button><br>
		<a href="<?=base_url()?>app/register">Register</a>
	</form>
</div>