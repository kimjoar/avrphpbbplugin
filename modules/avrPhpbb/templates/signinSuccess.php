<div class="span-16 append-1">
  <?php echo form_tag('avrPhpbb/signin', array('name' => 'login')) ?>
  
    <fieldset>
      <legend>Your details</legend>

      <div class="field">
        <?php echo label_for('username', 'Username') ?>
        <?php echo input_tag('username', null, array('class' => 'text user', 'tabindex' => ++$tab)) ?>
        <p><?php echo link_to('Forgot your username?', '@forgot_type?type=username') ?></p>
      </div>

      <div class="field">
        <?php echo label_for('password', 'Password') ?>
        <?php echo input_password_tag('password', null, array('class' => 'text password', 'tabindex' => ++$tab)) ?>
        <p><?php echo link_to('Forgot your password?', '@forgot_type?type=password') ?></p>
      </div>

      <?php if (sfConfig::get('app_users_enable_remember_me', false)) : ?>
        <div class="field">
          <?php echo label_for('remember', 'Remember me') ?>
          <?php echo checkbox_tag('remember', 1, false, array('class' => 'checkbox', 'tabindex' => ++$tab)) ?>
        </div>
      <?php endif; ?>

      <div class="field last">
        <label>&nbsp;</label>
        <?php echo submit_tag('Log me in please!', array('class' => 'submit', 'tabindex' => ++$tab)) ?>
      </div>
    </fieldset>
  </form>
</div>

<div class="span-7 last">
  <h3>Not registered?</h3>
  <p>Create a <?php echo link_to('new account', '@register') ?> now! It's free, and only takes a couple of minutes.</p>
</div>
