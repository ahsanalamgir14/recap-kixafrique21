<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Email_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
	}

	public function send_email_verification_mail($to = "", $verification_code = "")
	{
		$to_name = $this->db->get_where('users', array('email' => $to))->row_array();

		$email_data['subject'] = "Verify email address";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $to;
		$email_data['to_name'] = $to_name['first_name'] . ' ' . $to_name['last_name'];
		$email_data['verification_code'] = $verification_code;
		$email_template = $this->load->view('email/email_verification', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from'], 'verification');
	}


	function password_reset_email($verification_code = '', $email = '')
	{
		$query = $this->db->get_where('users', array('email' => $email));
		if ($query->num_rows() > 0) {
			$email_data['subject'] = "Password reset request";
			$email_data['from'] = get_settings('system_email');
			$email_data['to'] = $email;
			$email_data['to_name'] = $query->row('first_name') . ' ' . $query->row('last_name');
			$email_data['message'] = 'You have requested a change of password from ' . get_settings('system_name') . '. Please change your new password from this link : <b style="cursor: pointer;"><u>' . site_url('login/change_password/') . $verification_code . '</u></b><br><br><p>Please use this link in under 15 minutes.</p>';
			$email_template = $this->load->view('email/common_template', $email_data, TRUE);
			$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
			return true;
		} else {
			return false;
		}
	}

	public function send_mail_on_course_status_changing($course_id = "", $mail_subject = "", $mail_body = "")
	{
		$instructor_id		 = 0;
		$course_details    = $this->crud_model->get_course_by_id($course_id)->row_array();
		if ($course_details['user_id'] != "") {
			$instructor_id = $course_details['user_id'];
		} else {
			$instructor_id = $this->session->userdata('user_id');
		}
		$instuctor_details = $this->user_model->get_all_user($instructor_id)->row_array();


		$email_data['subject'] = $mail_subject;
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $instuctor_details['email'];
		$email_data['to_name'] = $instuctor_details['first_name'] . ' ' . $instuctor_details['last_name'];
		$email_data['message'] = $mail_body;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function course_purchase_notification($student_id = "", $payment_method = "", $amount_paid = "")
	{
		$purchased_courses 	= $this->session->userdata('cart_items');
		$student_data 		= $this->user_model->get_all_user($student_id)->row_array();
		$student_full_name 	= $student_data['first_name'] . ' ' . $student_data['last_name'];
		$admin_id 			= $this->user_model->get_admin_details()->row('id');
		foreach ($purchased_courses as $course_id) {
			$course_owner_user_id = $this->crud_model->get_course_by_id($course_id)->row('user_id');
			if ($course_owner_user_id != $admin_id) :
				$this->course_purchase_notification_admin($course_id, $student_full_name, $student_data['email'], $amount_paid);
			endif;
			$this->course_purchase_notification_instructor($course_id, $student_full_name, $student_data['email']);
			$this->course_purchase_notification_student($course_id, $student_id);
		}
	}

	public function course_purchase_notification_admin($course_id = "", $student_full_name = "", $student_email = "", $amount = "")
	{
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		$admin_details = $this->user_model->get_admin_details();
		$instructor_details = $this->user_model->get_all_user($course_details['user_id'])->row_array();
		$admin_msg = "<h2>" . $course_details['title'] . "</h2>";
		$admin_msg .= "<h3><b><u><span style='color: #2ec75e;'>Course Price : " . currency($amount) . "</span></u></b></h3>";
		$admin_msg .= "<p><b>Course owner:</b></p>";
		$admin_msg .= "<p>Name: <b>" . $instructor_details['first_name'] . " " . $instructor_details['last_name'] . "</b></p>";
		$admin_msg .= "<p>Email: <b>" . $instructor_details['email'] . "</b></p>";
		$admin_msg .= "<hr style='opacity: .4;'>";
		$admin_msg .= "<p><b>Bought the course:-</b></p>";
		$admin_msg .= "<p>Name: <b>" . $student_full_name . "</b></p>";
		$admin_msg .= "<p>Email: <b>" . $student_email . "</b></p>";


		$email_data['subject'] = 'The course has sold out';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $admin_details->row('email');
		$email_data['to_name'] = $admin_details->row('first_name') . ' ' . $admin_details->row('last_name');
		$email_data['message'] = $admin_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function course_purchase_notification_instructor($course_id = "", $student_full_name = "", $student_email = "")
	{
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		$instructor_details = $this->user_model->get_all_user($course_details['user_id']);
		$instructor_msg = "<h2>" . $course_details['title'] . "</h2>";
		$instructor_msg .= "<p>Congratulation!! Your <b>" . $course_details['title'] . "</b> courses have been sold.</p>";
		$instructor_msg .= "<p><b>Bought the course:-</b></p>";
		$instructor_msg .= "<p>Name: <b>" . $student_full_name . "</b></p>";
		$instructor_msg .= "<p>Email: <b>" . $student_email . "</b></p>";

		$email_data['subject'] = 'The course has sold out';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $instructor_details->row('email');
		$email_data['to_name'] = $instructor_details->row('first_name') . ' ' . $instructor_details->row('last_name');
		$email_data['message'] = $instructor_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function course_purchase_notification_student($course_id = "", $student_id = "")
	{
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		$student_details = $this->user_model->get_all_user($student_id);
		$instructor_details = $this->user_model->get_all_user($course_details['user_id'])->row_array();
		$student_msg = "<h2>" . $course_details['title'] . "</h2>";
		$student_msg .= "<p><b>Congratulation!!</b> You have purchased a <b>" . $course_details['title'] . "</b> course.</p>";
		$student_msg .= "<hr style='opacity: .4;'>";
		$student_msg .= "<p><b>Course owner:</b></p>";
		$student_msg .= "<p>Name: <b>" . $instructor_details['first_name'] . " " . $instructor_details['last_name'] . "</b></p>";
		$student_msg .= "<p>Email: <b>" . $instructor_details['email'] . "</b></p>";

		$email_data['subject'] = 'Course Purchase';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $student_details->row('email');
		$email_data['to_name'] = $student_details->row('first_name') . ' ' . $student_details->row('last_name');
		$email_data['message'] = $student_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function notify_on_certificate_generate($user_id = "", $course_id = "")
	{
		$checker = array(
			'course_id' => $course_id,
			'student_id' => $user_id,
		);
		$result = $this->db->get_where('certificates', $checker)->row_array();
		$certificate_link = site_url('certificate/' . $result['shareable_url']);
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		$user_details = $this->user_model->get_all_user($user_id)->row_array();
		$email_msg = "<b>Congratulations!!</b> " . $user_details['first_name'] . " " . $user_details['last_name'] . ",";
		$email_msg .= "<p>You have successfully completed the course named, <b>" . $course_details['title'] . ".</b></p>";
		$email_msg .= "<p>You can get your course completion certificate from here <b>" . $certificate_link . ".</b></p>";
		$email_data['subject'] = 'Course Completion Notification';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $user_details['email'];
		$email_data['to_name'] = $user_details['first_name'] . ' ' . $user_details['last_name'];
		$email_data['message'] = $email_msg;
		$email_template = $this->load->view('email/common_template', $email_data, true);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function suspended_offline_payment($user_id = "")
	{
		$user_details = $this->user_model->get_all_user($user_id);
		$email_msg  = "<p>Your offline payment has been <b style='color: red;'>suspended</b> !</p>";
		$email_msg .= "<p>Please provide a valid document of your payment.</p>";

		$email_data['subject'] = 'Suspended Offline Payment';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $user_details->row('email');
		$email_data['to_name'] = $user_details->row('first_name') . ' ' . $user_details->row('last_name');
		$email_data['message'] = $email_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}


	public function bundle_purchase_notification($student_id = "", $payment_method = "", $amount_paid = "")
	{
		$bundle_id = $this->session->userdata('checkout_bundle_id');
		$bundle_details = $this->course_bundle_model->get_bundle($bundle_id)->row_array();

		$admin_details = $this->user_model->get_admin_details()->row_array();
		$bundle_creator_details = $this->user_model->get_all_user($bundle_details['user_id'])->row_array();
		$student_details = $this->user_model->get_all_user($student_id)->row_array();

		if ($admin_details['id'] != $bundle_creator_details['id']) {
			$this->bundle_purchase_notification_admin($bundle_details, $admin_details, $bundle_creator_details, $student_details);
		}
		$this->bundle_purchase_notification_bundle_creator($bundle_details, $admin_details, $bundle_creator_details, $student_details);
		$this->bundle_purchase_notification_student($bundle_details, $admin_details, $bundle_creator_details, $student_details);
	}

	function bundle_purchase_notification_admin($bundle_details = "", $admin_details = "", $bundle_creator_details = "", $student_details = "")
	{
		$email_msg = "<h2>" . $bundle_details['title'] . "</h2>";
		$email_msg .= "<h3><b><u><span style='color: #2ec75e;'>Bundle Price : " . currency($bundle_details['price']) . "</span></u></b></h3>";
		$email_msg .= "<p><b>Bundle owner:</b></p>";
		$email_msg .= "<p>Name: <b>" . $bundle_creator_details['first_name'] . " " . $bundle_creator_details['last_name'] . "</b></p>";
		$email_msg .= "<p>Email: <b>" . $bundle_creator_details['email'] . "</b></p>";
		$email_msg .= "<hr style='opacity: .4;'>";
		$email_msg .= "<p><b>Bought the bundle:-</b></p>";
		$email_msg .= "<p>Name: <b>" . $student_details['first_name'] . " " . $student_details['last_name'] . "</b></p>";
		$email_msg .= "<p>Email: <b>" . $student_details['email'] . "</b></p>";

		$email_data['subject'] = 'The bundle has sold out';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $admin_details['email'];
		$email_data['to_name'] = $admin_details['first_name'] . ' ' . $admin_details['last_name'];
		$email_data['message'] = $email_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	function bundle_purchase_notification_bundle_creator($bundle_details = "", $admin_details = "", $bundle_creator_details = "", $student_details = "")
	{
		$email_msg = "<h2>" . $bundle_details['title'] . "</h2>";
		$email_msg .= "<p>Congratulation!! Your <b>" . $bundle_details['title'] . "</b> course bundle have been sold.</p>";
		$email_msg .= "<h3><b><u><span style='color: #2ec75e;'>Bundle Price : " . currency($bundle_details['price']) . "</span></u></b></h3>";
		$email_msg .= "<p><b>Bought the bundle:-</b></p>";
		$email_msg .= "<p>Name: <b>" . $student_details['first_name'] . ' ' . $student_details['last_name'] . "</b></p>";
		$email_msg .= "<p>Email: <b>" . $student_details['email'] . "</b></p>";

		$email_data['subject'] = 'The bundle has sold out';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $bundle_creator_details['email'];
		$email_data['to_name'] = $bundle_creator_details['first_name'] . ' ' . $bundle_creator_details['last_name'];
		$email_data['message'] = $email_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	function bundle_purchase_notification_student($bundle_details = "", $admin_details = "", $bundle_creator_details = "", $student_details = "")
	{
		$email_msg = "<h2>" . $bundle_details['title'] . "</h2>";
		$email_msg .= "<p><b>Congratulation!!</b> You have purchased a <b>" . $bundle_details['title'] . "</b> bundle.</p>";
		$email_msg .= "<h3><b><u><span style='color: #2ec75e;'>Bundle Price : " . currency($bundle_details['price']) . "</span></u></b></h3>";
		$email_msg .= "<hr style='opacity: .4;'>";
		$email_msg .= "<p><b>Bundle owner:</b></p>";
		$email_msg .= "<p>Name: <b>" . $bundle_creator_details['first_name'] . " " . $bundle_creator_details['last_name'] . "</b></p>";
		$email_msg .= "<p>Email: <b>" . $bundle_creator_details['email'] . "</b></p>";

		$email_data['subject'] = 'Bundle Purchase';
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $student_details['email'];
		$email_data['to_name'] = $student_details['first_name'] . ' ' . $student_details['last_name'];
		$email_data['message'] = $email_msg;
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	function send_notice($notice_id = "", $course_id = "")
	{
		$course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
		$notice_details = $this->noticeboard_model->get_notices($notice_id)->row_array();
		$instructor_details = $this->user_model->get_all_user($course_details['user_id'])->row_array();

		$email_data['subject'] = htmlspecialchars_decode($notice_details['title']);
		$email_data['from'] = get_settings('system_email');
		$email_data['course_title'] = $course_details['title'];

		$enrolled_students = $this->crud_model->enrol_history($course_id)->result_array();
		foreach ($enrolled_students as $enrolled_student) :
			$student_details = $this->user_model->get_user($enrolled_student['user_id'])->row_array();
			$email_data['to'] = $student_details['email'];
			$email_data['to_name'] = $student_details['first_name'] . ' ' . $student_details['last_name'];
			$email_data['message'] = htmlspecialchars_decode($notice_details['description']) . '<hr style="border: 1px solid #efefef; margin-top: 50px;"> <small><b>' . get_phrase('course') . ':</b> ' . $course_details['title'] . '<br> <b>' . get_phrase('instructor') . ': </b> ' . $instructor_details['first_name'] . ' ' . $instructor_details['last_name'] . '</small>';

			$email_template = $this->load->view('email/common_template', $email_data, TRUE);
			$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
		endforeach;

		return 1;
	}

	function live_class_invitation_mail($to = "")
	{
		$query = $this->db->get_where('users', array('email' => $to));
		$email_data['subject'] = "Your live class started";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $to;
		$email_data['to_name'] = $query->row('first_name') . ' ' . $query->row('last_name');
		$email_data['message'] = $this->input->post('jitsi_live_alert_message');
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
		return true;
	}

	function new_device_login_alert($user_id = "")
	{
		$this->load->library('user_agent');

		if ($this->agent->is_browser()) {
			$agent = $this->agent->browser() . ' ' . $this->agent->version();
		} elseif ($this->agent->is_robot()) {
			$agent = $this->agent->robot();
		} elseif ($this->agent->is_mobile()) {
			$agent = $this->agent->mobile();
		} else {
			$agent = 'Unidentified User Agent';
		}

		$browser = $agent;
		$device = $this->agent->platform();

		if (!$this->session->userdata('new_device_verification_code')) {
			$new_device_verification_code = rand(100000, 999999);
		} else {
			$new_device_verification_code = $this->session->userdata('new_device_verification_code');
		}
		if ($user_id == "") {
			$user_id = $this->session->userdata('new_device_user_id');
		}

		$row = $this->db->get_where('users', array('id' => $user_id))->row_array();
		$email_data['subject'] = "New device login alert";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $row['email'];
		$email_data['to_name'] = $row['first_name'] . ' ' . $row['last_name'];
		$email_data['message'] = "Have you tried logging in with a different device? Confirm using the verification code.<br> Your verification code is <b>" . $new_device_verification_code . '</b><br> Remember that you will lose access to your previous device after logging in to the new device(' . $browser . ' ' . $device . ').<p>Use the verification code within <b>10</b> minutes</p>';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);

		//600 seconds = 10 minutes
		$this->session->set_userdata('new_device_code_expiration_time', (time() + 600));
		$this->session->set_userdata('new_device_user_email', $row['email']);
		$this->session->set_userdata('new_device_user_id', $user_id);
		$this->session->set_userdata('new_device_verification_code', $new_device_verification_code);

		return true;
	}

	//course_addon start

	public function become_a_course_affiliator_by_admin($email = "", $name = "", $password = "")
	{


		$email_data['subject'] = "Assigned as affiliator  ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Congratulation ! You are assigned as an affiliator . Your password is ' . $password . ' ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}
	public function send_email_when_approed_an_affiliator($email = "", $name = "")
	{


		$email_data['subject'] = "Assigned as affiliator  ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Congratulation ! You are assigned as an affiliator ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_when_delete_an_affiliator_request($email = "", $name = "")
	{


		$email_data['subject'] = "Cancellation  ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Sorry ! Your request has been currently refused  ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_when_suspend_an_affiliator_request($email = "", $name = "")
	{


		$email_data['subject'] = "Suspension";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Sorry ! You have been suspended from affliation  ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	
	public function send_email_when_reactove_an_affiliator_request($email = "", $name = "")
	{


		$email_data['subject'] = " Affiliate activation";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Congratulation ! your suspention has been cancelled . Now you are reassigned as an affiliator   ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_when_withdrawl_request_for_affiliator_approved($email = "", $name = "")
	{


		$email_data['subject'] = "Payment Request Successfull";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = 'Congartulation ! Your payment request has been approved ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_when_make_withdrawl_request($email = "", $name = "",$amount="")
	{
	

		$email_data['subject'] = "Payment Request send ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = ' Your Withdrawal request of '.currency($amount).' has been sent to admin ';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_to_admin_when_withdrawl_request_made_by_affiliator ($email = "", $name = "",$user="",$amount="")
	{


		$email_data['subject'] = "Withdrawal Request ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = ''.$user.' has made a withdrawl request of '.currency($amount).'$';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}

	public function send_email_to_admin_when_withdrawl_pending_request_cancle($email = "", $name = "",$user="")
	{


		$email_data['subject'] = "Withdrawal Request Cancelation ";
		$email_data['from'] = get_settings('system_email');
		$email_data['to'] = $email;
		$email_data['to_name'] = $name;
		$email_data['message'] = ' '.$user.' has cancelled a pending request';
		$email_template = $this->load->view('email/common_template', $email_data, TRUE);
		$this->send_smtp_mail($email_template, $email_data['subject'], $email_data['to'], $email_data['from']);
	}





	//course_addon end






	public function send_smtp_mail($msg = NULL, $sub = NULL, $to = NULL, $from = NULL, $email_type = NULL, $verification_code = null)
	{
		//Load email library
		$this->load->library('email');

		if ($from == NULL)
			$from		=	$this->db->get_where('settings', array('key' => 'system_email'))->row()->value;

		//SMTP & mail configuration
		$config = array(
			'protocol'  => get_settings('protocol'),
			'smtp_host' => get_settings('smtp_host'),
			'smtp_port' => get_settings('smtp_port'),
			'smtp_user' => get_settings('smtp_user'),
			'smtp_pass' => get_settings('smtp_pass'),
			'smtp_crypto' => get_settings('smtp_crypto'), //can be 'ssl' or 'tls' for example
			'mailtype'  => 'html',
			'newline'   => "\r\n",
			'charset'   => 'utf-8',
			'smtp_timeout' => '10', //in seconds
		);
		$this->email->set_header('MIME-Version', 1.0);
		$this->email->set_header('Content-type', 'text/html');
		$this->email->set_header('charset', 'UTF-8');

		$this->email->initialize($config);

		$this->email->to($to);
		$this->email->from($from, get_settings('system_name'));
		$this->email->subject($sub);
		$this->email->message($msg);

		//Send email
		$this->email->send();
		// echo $this->email->print_debugger();
		// die();
	}
}
