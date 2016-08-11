<!-- app/View/Users/add.ctp -->
<div class="users form">
<?php echo $this->Form->create('User'); ?>
    <fieldset>
        <legend><?php echo __('Add User'); ?></legend>
        <?php 
			echo $this->Form->input('email');
			echo $this->Form->input('password');
			echo $this->Form->input('verifypassword', array('type' => 'password', 'label' => 'Verify Password'));
			echo $this->Form->input('owner', array('label' => 'Name'));
			echo $this->Form->input('name', array('label' => 'User Name'));
		?>
    </fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>