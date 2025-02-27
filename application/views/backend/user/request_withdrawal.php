<?php
$total_pending_amount  = $this->crud_model->get_total_pending_amount($this->session->userdata('user_id'));
$requested_withdrawals = $this->crud_model->get_requested_withdrawals($this->session->userdata('user_id'));

if(addon_status('ebook')){
    $this->db->select_sum('instructor_revenue');
    $this->db->where('ebook.user_id', $this->session->userdata('user_id'));
    $this->db->where('ebook_payment.instructor_payment_status', 0);
    $this->db->from('ebook_payment');
    $this->db->join('ebook', 'ebook_payment.ebook_id = ebook.ebook_id'); 
    $ebook_total_pending_amount = $this->db->get()->row('instructor_revenue');
    $total_pending_amount = $total_pending_amount + $ebook_total_pending_amount;
}

if(addon_status('tutor_booking')){
    $this->db->select_sum('instructor_revenue');
    $this->db->where('tutor_id', $this->session->userdata('user_id'));
    $this->db->from('tutor_payment');
    $tutor_total_pending_amount = $this->db->get()->row('instructor_revenue');

    $total_pending_amount = $total_pending_amount + $tutor_total_pending_amount;
}

?>
<?php if ($requested_withdrawals->num_rows() > 0): ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><?php echo get_phrase('oops'); ?>!</h4>
        <p><strong><?php echo get_phrase('you_already_requested_a_withdrawal'); ?></strong></p>
        <p><?php echo get_phrase('if_you_want_to_make_another'); ?>, <?php echo get_phrase('you_have_to_delete_the_requested_one_first'); ?></p>
    </div>
<?php elseif($total_pending_amount == 0): ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><?php echo get_phrase('oops'); ?>!</h4>
        <p><strong><?php echo get_phrase('you_got_nothing_to_withdraw'); ?></strong></p>
    </div>
<?php else: ?>
    <form class="required-form" action="<?php echo site_url('user/withdrawal/request'); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $this->session->userdata('user_id'); ?>">
        <div class="form-group">
            <label for="withdrawal_amount"><?php echo get_phrase('withdrawal_amount'); ?></label>
            <input type="number" class="form-control" name="withdrawal_amount" id="withdrawal_amount" aria-describedby="withdrawal_amount-help" placeholder="<?php echo get_phrase('withdrawal_amount_has_to_be_less_than_or_equal_to').' '.$total_pending_amount; ?>" min="0" max="<?php echo $total_pending_amount; ?>" required>
            <small id="withdrawal_amount-help" class="form-text text-muted"><?php echo get_phrase('withdrawal_amount_has_to_be_less_than_or_equal_to').' '.$total_pending_amount; ?></small>
        </div>
        <button type="button" class="btn btn-primary text-center" onclick="checkRequiredFields()"><?php echo get_phrase('request'); ?></button>
    </form>
<?php endif; ?>
