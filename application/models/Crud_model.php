<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (file_exists("application/aws-module/aws-autoloader.php")) {
    include APPPATH . 'aws-module/aws-autoloader.php';
}
//v5.7
class Crud_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function get_categories($param1 = "")
    {
        if ($param1 != "") {
            $this->db->where('id', $param1);
        }
        $this->db->where('parent', 0);
        return $this->db->get('category');
    }

    public function get_category_details_by_id($id)
    {
        return $this->db->get_where('category', array('id' => $id));
    }

    public function get_category_id($slug = "")
    {
        $category_details = $this->db->get_where('category', array('slug' => $slug))->row_array();
        return $category_details['id'];
    }

    public function add_category()
    {
        $data['code']   = html_escape($this->input->post('code'));
        $data['name']   = html_escape($this->input->post('name'));
        $data['parent'] = html_escape($this->input->post('parent'));
        $data['slug']   = slugify(html_escape($this->input->post('name')));

        // CHECK IF THE CATEGORY NAME ALREADY EXISTS
        $this->db->where('name', $data['name']);
        $this->db->or_where('slug', $data['slug']);
        $previous_data = $this->db->get('category')->num_rows();

        if ($previous_data == 0) {
            // Font awesome class adding
            if ($_POST['font_awesome_class'] != "") {
                $data['font_awesome_class'] = html_escape($this->input->post('font_awesome_class'));
            } else {
                $data['font_awesome_class'] = 'fas fa-chess';
            }

            if ($this->input->post('parent') == 0) {
                // category thumbnail adding
                if (!file_exists('uploads/thumbnails/category_thumbnails')) {
                    mkdir('uploads/thumbnails/category_thumbnails', 0777, true);
                }
                if ($_FILES['category_thumbnail']['name'] == "") {
                    $data['thumbnail'] = 'category-thumbnail.png';
                } else {
                    $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
                    move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/thumbnails/category_thumbnails/' . $data['thumbnail']);
                }
            }
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('category', $data);
            return true;
        }

        return false;
    }

    public function edit_category($param1)
    {
        $data['name']   = html_escape($this->input->post('name'));
        $data['parent'] = html_escape($this->input->post('parent'));
        $data['slug']   = slugify(html_escape($this->input->post('name')));

        // CHECK IF THE CATEGORY NAME ALREADY EXISTS
        $this->db->where('name', $data['name']);
        $this->db->or_where('slug', $data['slug']);
        $previous_data = $this->db->get('category')->result_array();

        $checker = true;
        foreach ($previous_data as $row) {
            if ($row['id'] != $param1) {
                $checker = false;
                break;
            }
        }

        if ($checker) {
            // Font awesome class adding
            if ($_POST['font_awesome_class'] != "") {
                $data['font_awesome_class'] = html_escape($this->input->post('font_awesome_class'));
            } else {
                $data['font_awesome_class'] = 'fas fa-chess';
            }

            if ($this->input->post('parent') == 0) {
                // category thumbnail adding
                if (!file_exists('uploads/thumbnails/category_thumbnails')) {
                    mkdir('uploads/thumbnails/category_thumbnails', 0777, true);
                }
                if ($_FILES['category_thumbnail']['name'] != "") {
                    $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
                    move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/thumbnails/category_thumbnails/' . $data['thumbnail']);
                }
            }
            $data['last_modified'] = strtotime(date('D, d-M-Y'));
            $this->db->where('id', $param1);
            $this->db->update('category', $data);

            return true;
        }
        return false;
    }

    public function delete_category($category_id)
    {
        $this->db->where('id', $category_id);
        $this->db->delete('category');

        $this->db->where('parent', $category_id);
        $this->db->delete('category');
    }

    public function get_sub_categories($parent_id = "")
    {
        return $this->db->get_where('category', array('parent' => $parent_id))->result_array();
    }

    public function enrol_history($course_id = "")
    {
        if ($course_id > 0) {
            return $this->db->get_where('enrol', array('course_id' => $course_id));
        } else {
            return $this->db->get('enrol');
        }
    }

    public function enrol_history_by_user_id($user_id = "")
    {
        return $this->db->get_where('enrol', array('user_id' => $user_id));
    }

    public function all_enrolled_student()
    {
        $this->db->select('user_id');
        $this->db->distinct('user_id');
        return $this->db->get('enrol');
    }

    public function enrol_history_by_date_range($timestamp_start = "", $timestamp_end = "")
    {
        $this->db->order_by('date_added', 'desc');
        $this->db->where('date_added >=', $timestamp_start);
        $this->db->where('date_added <=', $timestamp_end);
        return $this->db->get('enrol');
    }

    public function get_revenue_by_user_type($timestamp_start = "", $timestamp_end = "", $revenue_type = "")
    {
        $course_ids = array();
        $courses    = array();
        $admin_details = $this->user_model->get_admin_details()->row_array();
        if ($revenue_type == 'admin_revenue') {
            $this->db->where('date_added >=', $timestamp_start);
            $this->db->where('date_added <=', $timestamp_end);
        } elseif ($revenue_type == 'instructor_revenue') {

            $this->db->where('user_id !=', $admin_details['id']);
            $this->db->select('id');
            $courses = $this->db->get('course')->result_array();
            foreach ($courses as $course) {
                if (!in_array($course['id'], $course_ids)) {
                    array_push($course_ids, $course['id']);
                }
            }
            if (sizeof($course_ids)) {
                $this->db->where_in('course_id', $course_ids);
            } else {
                return array();
            }
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('payment')->result_array();
    }

    public function get_instructor_revenue($user_id = "", $timestamp_start = "", $timestamp_end = "")
    {
        $course_ids = array();
        $courses    = array();

        // $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($user_id);

        // if ($user_id > 0) {
        //     $this->db->where('user_id', $user_id);
        // } else {
        //     $user_id = $this->session->userdata('user_id');
        //     $this->db->where('user_id', $user_id);
        // }

        // if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
        //     $this->db->or_where_in('id', $multi_instructor_course_ids);
        //     $this->db->where('creator', $user_id);
        // }

        

        //revenue only showing on course creator panel
        if ($user_id > 0) {
            $this->db->where('creator', $user_id);
        } else {
            $this->db->where('creator', $this->session->userdata('user_id'));
        }

        $this->db->select('id');
        $courses = $this->db->get('course')->result_array();
        foreach ($courses as $course) {
            if (!in_array($course['id'], $course_ids)) {
                array_push($course_ids, $course['id']);
            }
        }
        if (sizeof($course_ids)) {
            $this->db->where_in('course_id', $course_ids);
        } else {
            return array();
        }

        // CHECK IF THE DATE RANGE IS SELECTED
        if (!empty($timestamp_start) && !empty($timestamp_end)) {
            $this->db->where('date_added >=', $timestamp_start);
            $this->db->where('date_added <=', $timestamp_end);
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('payment')->result_array();
    }

    public function delete_payment_history($param1)
    {
        $this->db->where('id', $param1);
        $this->db->delete('payment');
    }
    public function delete_enrol_history($param1)
    {
        $this->db->where('id', $param1);
        $this->db->delete('enrol');
    }

    public function purchase_history($user_id = "")
    {
        if ($user_id > 0) {
            return $this->db->get_where('payment', array('user_id' => $user_id));
        } else {
            return $this->db->get('payment');
        }
    }

    public function get_payment_details_by_id($payment_id = "")
    {
        return $this->db->get_where('payment', array('id' => $payment_id))->row_array();
    }

    public function update_payout_status($payout_id = "", $payment_type = "")
    {
        $updater = array(
            'status' => 1,
            'payment_type' => $payment_type,
            'last_modified' => strtotime(date('D, d-M-Y'))
        );
        $this->db->where('id', $payout_id);
        $this->db->update('payout', $updater);
    }

    public function update_system_settings()
    {
        $data['value'] = html_escape($this->input->post('system_name'));
        $this->db->where('key', 'system_name');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('system_title'));
        $this->db->where('key', 'system_title');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('author'));
        $this->db->where('key', 'author');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('slogan'));
        $this->db->where('key', 'slogan');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('language'));
        $this->db->where('key', 'language');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('text_align'));
        $this->db->where('key', 'text_align');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('system_email'));
        $this->db->where('key', 'system_email');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('address'));
        $this->db->where('key', 'address');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('phone'));
        $this->db->where('key', 'phone');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('youtube_api_key'));
        $this->db->where('key', 'youtube_api_key');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('vimeo_api_key'));
        $this->db->where('key', 'vimeo_api_key');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('purchase_code'));
        $this->db->where('key', 'purchase_code');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('footer_text'));
        $this->db->where('key', 'footer_text');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('footer_link'));
        $this->db->where('key', 'footer_link');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('website_keywords'));
        $this->db->where('key', 'website_keywords');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('website_description'));
        $this->db->where('key', 'website_description');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('student_email_verification'));
        $this->db->where('key', 'student_email_verification');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('course_accessibility'));
        $this->db->where('key', 'course_accessibility');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('allowed_device_number_of_loging'));
        if ($data['value'] < 1 || !is_numeric($data['value'])) {
            $data['value'] = 1;
        }
        $this->db->where('key', 'allowed_device_number_of_loging');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('course_selling_tax'));
        $this->db->where('key', 'course_selling_tax');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('google_analytics_id'));
        $this->db->where('key', 'google_analytics_id');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('meta_pixel_id'));
        $this->db->where('key', 'meta_pixel_id');
        $this->db->update('settings', $data);
    }

    public function update_smtp_settings()
    {
        $data['value'] = html_escape($this->input->post('protocol'));
        $this->db->where('key', 'protocol');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_crypto'));
        $this->db->where('key', 'smtp_crypto');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_host'));
        $this->db->where('key', 'smtp_host');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_port'));
        $this->db->where('key', 'smtp_port');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_user'));
        $this->db->where('key', 'smtp_user');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_pass'));
        $this->db->where('key', 'smtp_pass');
        $this->db->update('settings', $data);
    }

    public function update_social_login_settings()
    {
        $data['value'] = html_escape($this->input->post('fb_social_login'));
        $this->db->where('key', 'fb_social_login');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('fb_app_id'));
        $this->db->where('key', 'fb_app_id');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('fb_app_secret'));
        $this->db->where('key', 'fb_app_secret');
        $this->db->update('settings', $data);
    }

    function get_payment_gateways($identifier = ""){
        if($identifier){
            $this->db->where('identifier', $identifier);
        }
        return $this->db->get('payment_gateways');
    }

    public function update_payment_settings()
    {
        $keys = array();
        $data['title'] = ucfirst($this->input->post('identifier'));
        $data['identifier'] = $this->input->post('identifier');
        $data['currency'] = $this->input->post('currency');
        $data['enabled_test_mode'] = $this->input->post('enabled_test_mode');
        $data['status'] = $this->input->post('status');

        foreach($_POST as $key => $post_value):
            if(!array_key_exists($key,$data)){
                $keys[$key] = $post_value;
            }
        endforeach;
        $data['keys'] = json_encode($keys);

        if($this->get_payment_gateways($data['identifier'])->num_rows() > 0){
            $data['updated_at'] = time();
            $this->db->where('identifier', $data['identifier']);
            $this->db->update('payment_gateways', $data);
        }else{
            $data['created_at'] = time();
            $this->db->insert('payment_gateways', $data);
        }
        $this->session->set_flashdata('flash_message', get_phrase('payment_settings_updated_successfully'));
    }

    public function update_stripe_settings()
    {
        // update stripe keys
        $stripe_info = array();

        $stripe['active'] = $this->input->post('stripe_active');
        $stripe['testmode'] = $this->input->post('testmode');
        $stripe['public_key'] = $this->input->post('public_key');
        $stripe['secret_key'] = $this->input->post('secret_key');
        $stripe['public_live_key'] = $this->input->post('public_live_key');
        $stripe['secret_live_key'] = $this->input->post('secret_live_key');

        array_push($stripe_info, $stripe);

        $data['value']    =   json_encode($stripe_info);
        $this->db->where('key', 'stripe_keys');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('stripe_currency'));
        $this->db->where('key', 'stripe_currency');
        $this->db->update('settings', $data);
    }

    public function update_razorpay_settings()
    {
        // update razorpay keys
        $paytm_info = array();
        $razorpay['active'] = htmlspecialchars($this->input->post('razorpay_active'));
        $razorpay['key'] = htmlspecialchars($this->input->post('key'));
        $razorpay['secret_key'] = htmlspecialchars($this->input->post('secret_key'));
        $razorpay['theme_color'] = htmlspecialchars($this->input->post('theme_color'));

        array_push($paytm_info, $razorpay);

        $data['value']    =   json_encode($paytm_info);
        $this->db->where('key', 'razorpay_keys');
        $this->db->update('settings', $data);

        $data['value'] = htmlspecialchars($this->input->post('razorpay_currency'));
        $this->db->where('key', 'razorpay_currency');
        $this->db->update('settings', $data);
    }

    public function update_system_currency()
    {
        $data['value'] = html_escape($this->input->post('system_currency'));
        $this->db->where('key', 'system_currency');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('currency_position'));
        $this->db->where('key', 'currency_position');
        $this->db->update('settings', $data);
    }

    public function update_instructor_settings()
    {
        if (isset($_POST['allow_instructor'])) {
            $data['value'] = html_escape($this->input->post('allow_instructor'));
            $this->db->where('key', 'allow_instructor');
            $this->db->update('settings', $data);
        }

        if (isset($_POST['instructor_revenue'])) {
            $data['value'] = html_escape($this->input->post('instructor_revenue'));
            $this->db->where('key', 'instructor_revenue');
            $this->db->update('settings', $data);
        }

        if (isset($_POST['instructor_application_note'])) {
            $data['value'] = html_escape($this->input->post('instructor_application_note'));
            $this->db->where('key', 'instructor_application_note');
            $this->db->update('settings', $data);
        }
    }

    public function get_lessons($type = "", $id = "")
    {
        $this->db->order_by("order", "asc");
        if ($type == "course") {
            return $this->db->get_where('lesson', array('course_id' => $id));
        } elseif ($type == "section") {
            return $this->db->get_where('lesson', array('section_id' => $id));
        } elseif ($type == "lesson") {
            return $this->db->get_where('lesson', array('id' => $id));
        } else {
            return $this->db->get('lesson');
        }
    }

    public function add_course($param1 = "")
    {
        $faqs = array();
        foreach(array_filter($this->input->post('faqs')) as $faq_key => $faq_title){
            $faqs[$faq_title] = $this->input->post('faq_descriptions')[$faq_key];
        }

        $outcomes = $this->trim_and_return_json($this->input->post('outcomes'));
        $requirements = $this->trim_and_return_json($this->input->post('requirements'));

        $data['course_type'] = html_escape($this->input->post('course_type'));
        $data['title'] = html_escape($this->input->post('title'));
        $data['short_description'] = html_escape($this->input->post('short_description'));
        $data['description'] = $this->input->post('description');
        $data['outcomes'] = $outcomes;
        $data['faqs'] = json_encode($faqs);
        $data['language'] = $this->input->post('language_made_in');
        $data['sub_category_id'] = $this->input->post('sub_category_id');
        $category_details = $this->get_category_details_by_id($this->input->post('sub_category_id'))->row_array();
        $data['category_id'] = $category_details['parent'];
        $data['requirements'] = $requirements;
        $data['price'] = $this->input->post('price');
        $data['discount_flag'] = $this->input->post('discount_flag');
        $data['discounted_price'] = $this->input->post('discounted_price');
        $data['level'] = $this->input->post('level');
        $data['is_free_course'] = $this->input->post('is_free_course');
        $data['video_url'] = html_escape($this->input->post('course_overview_url'));

        $enable_drip_content = $this->input->post('enable_drip_content');
        if (isset($enable_drip_content) && $enable_drip_content) {
            $data['enable_drip_content'] = 1;
        } else {
            $data['enable_drip_content'] = 0;
        }

        if ($this->input->post('course_overview_url') != "") {
            $data['course_overview_provider'] = html_escape($this->input->post('course_overview_provider'));
        } else {
            $data['course_overview_provider'] = "";
        }

        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['section'] = json_encode(array());
        $data['is_top_course'] = $this->input->post('is_top_course');
        $data['user_id'] = $this->session->userdata('user_id');
        $data['creator'] = $this->session->userdata('user_id');
        $data['meta_description'] = $this->input->post('meta_description');
        $data['meta_keywords'] = $this->input->post('meta_keywords');

        $admin_details = $this->user_model->get_admin_details()->row_array();
        if ($admin_details['id'] == $data['user_id']) {
            $data['is_admin'] = 1;
        } else {
            $data['is_admin'] = 0;
        }

        if($this->input->post('is_private') == 1){
            $data['status'] = 'private';
        }else{
            if ($param1 == "save_to_draft") {
                $data['status'] = 'draft';
            } else {
                if ($this->session->userdata('admin_login')) {
                    $data['status'] = 'active';
                } else {
                    $data['status'] = 'pending';
                }
            }
        }
        $this->db->insert('course', $data);

        $course_id = $this->db->insert_id();

        // Create folder if does not exist
        if (!file_exists('uploads/thumbnails/course_thumbnails')) {
            mkdir('uploads/thumbnails/course_thumbnails', 0777, true);
        }

        // Upload different number of images according to activated theme. Data is taking from the config.json file
        $course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
        foreach ($course_media_files as $course_media => $size) {
            if ($_FILES[$course_media]['name'] != "") {
                move_uploaded_file($_FILES[$course_media]['tmp_name'], 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg');
            }
        }

        if ($data['status'] == 'approved') {
            $this->session->set_flashdata('flash_message', get_phrase('course_added_successfully'));
        } elseif ($data['status'] == 'pending') {
            $this->session->set_flashdata('flash_message', get_phrase('course_added_successfully') . '. ' . get_phrase('please_wait_untill_Admin_approves_it'));
        } elseif ($data['status'] == 'draft') {
            $this->session->set_flashdata('flash_message', get_phrase('your_course_has_been_added_to_draft'));
        }

        $this->session->set_flashdata('flash_message', get_phrase('course_has_been_added_successfully'));
        return $course_id;
    }

    function add_shortcut_course($param1 = "")
    {
        $data['course_type'] = html_escape($this->input->post('course_type'));
        $data['title'] = html_escape($this->input->post('title'));
        $data['outcomes'] = '[]';
        $data['language'] = $this->input->post('language_made_in');
        $data['sub_category_id'] = $this->input->post('sub_category_id');
        $category_details = $this->get_category_details_by_id($this->input->post('sub_category_id'))->row_array();
        $data['category_id'] = $category_details['parent'];

        $data['requirements'] = '[]';
        $data['faqs'] = json_encode(array());
        $data['price'] = $this->input->post('price');
        $data['discount_flag'] = $this->input->post('discount_flag');
        $data['discounted_price'] = $this->input->post('discounted_price');
        $data['level'] = $this->input->post('level');
        $data['is_free_course'] = $this->input->post('is_free_course');

        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['section'] = json_encode(array());

        $data['user_id'] = $this->session->userdata('user_id');

        $admin_details = $this->user_model->get_admin_details()->row_array();
        if ($admin_details['id'] == $data['user_id']) {
            $data['is_admin'] = 1;
        } else {
            $data['is_admin'] = 0;
        }

        if($this->input->post('is_private') == 1){
            $data['status'] = 'private';
        }else{
            if ($param1 == "save_to_draft") {
                $data['status'] = 'draft';
            } else {
                if ($this->session->userdata('admin_login')) {
                    $data['status'] = 'active';
                } else {
                    $data['status'] = 'pending';
                }
            }
        }

        if ($data['is_free_course'] == 1 || $data['is_free_course'] != 1 && $data['price'] > 0 && $data['discount_flag'] != 1 || $data['discount_flag'] == 1 && $data['discounted_price'] > 0) {
            $this->db->insert('course', $data);

            $this->session->set_flashdata('flash_message', get_phrase('course_has_been_added_successfully'));

            $response['status'] = 1;
            return json_encode($response);
        } else {
            $response['status'] = 0;
            $response['message'] = get_phrase('please_fill_up_the_price_field');
            return json_encode($response);
        }
    }

    function trim_and_return_json($untrimmed_array)
    {
        $trimmed_array = array();
        if (sizeof($untrimmed_array) > 0) {
            foreach ($untrimmed_array as $row) {
                if ($row != "") {
                    array_push($trimmed_array, $row);
                }
            }
        }
        return json_encode($trimmed_array);
    }

    public function update_course($course_id, $type = "")
    {
        $course_details = $this->get_course_by_id($course_id)->row_array();

        $faqs = array();
        foreach(array_filter($this->input->post('faqs')) as $faq_key => $faq_title){
            $faqs[$faq_title] = $this->input->post('faq_descriptions')[$faq_key];
        }

        $outcomes = $this->trim_and_return_json($this->input->post('outcomes'));
        $requirements = $this->trim_and_return_json($this->input->post('requirements'));
        $data['title'] = $this->input->post('title');
        $data['short_description'] = html_escape($this->input->post('short_description'));
        $data['description'] = $this->input->post('description');
        $data['outcomes'] = $outcomes;
        $data['faqs'] = json_encode($faqs);
        $data['language'] = $this->input->post('language_made_in');
        $data['sub_category_id'] = $this->input->post('sub_category_id');
        $category_details = $this->get_category_details_by_id($this->input->post('sub_category_id'))->row_array();
        $data['category_id'] = $category_details['parent'];
        $data['requirements'] = $requirements;
        $data['is_free_course'] = $this->input->post('is_free_course');
        $data['price'] = $this->input->post('price');
        $data['discount_flag'] = $this->input->post('discount_flag');
        $data['discounted_price'] = $this->input->post('discounted_price');
        $data['level'] = $this->input->post('level');
        $data['video_url'] = $this->input->post('course_overview_url');

        $enable_drip_content = $this->input->post('enable_drip_content');
        if (isset($enable_drip_content) && $enable_drip_content) {
            $data['enable_drip_content'] = 1;
        } else {
            $data['enable_drip_content'] = 0;
        }

        if ($this->input->post('course_overview_url') != "") {
            $data['course_overview_provider'] = html_escape($this->input->post('course_overview_provider'));
        } else {
            $data['course_overview_provider'] = "";
        }

        $data['meta_description'] = $this->input->post('meta_description');
        $data['meta_keywords'] = $this->input->post('meta_keywords');
        $data['last_modified'] = time();

        if ($this->input->post('is_top_course') != 1) {
            $data['is_top_course'] = 0;
        } else {
            $data['is_top_course'] = 1;
        }


        if($this->input->post('is_private') == 1){
            $data['status'] = 'private';
        }else{
            if ($type == "save_to_draft") {
                $data['status'] = 'draft';
            } else {
                if ($this->session->userdata('admin_login')) {
                    if($course_details['status'] == 'private'){
                        if($this->is_course_instructor($course_details['id'], $this->session->userdata('user_id'))){
                            $data['status'] = 'active';
                        }else{
                            $data['status'] = 'pending';
                        }
                    }else{
                        $data['status'] = $course_details['status'];
                    }
                }else{
                    if($course_details['status'] == 'private'){
                        $data['status'] = 'pending';
                    }else{
                        $data['status'] = $course_details['status'];
                    }
                }
            }
        }

        // MULTI INSTRUCTOR PART STARTS
        if (isset($_POST['new_instructors']) && !empty($_POST['new_instructors'])) {
            $existing_instructors = explode(',', $course_details['user_id']);
            foreach ($_POST['new_instructors'] as $instructor) {
                if (!in_array($instructor, $existing_instructors)) {
                    array_push($existing_instructors, $instructor);
                }
            }
            $data['user_id'] = implode(",", $existing_instructors);
            $data['multi_instructor'] = 1;
        }
        // MULTI INSTRUCTOR PART ENDS


        // Upload different number of images according to activated theme. Data is taking from the config.json file
        $course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
        $previous_last_modified = $course_details['last_modified'];
        foreach ($course_media_files as $course_media => $size) {
            if ($_FILES[$course_media]['name'] != "") {

                move_uploaded_file($_FILES[$course_media]['tmp_name'], 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$data['last_modified']. '.jpg');

                if (file_exists('uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg')) {
                    unlink('uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg');
                }

                if (file_exists('uploads/thumbnails/course_thumbnails/optimized/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg')) {
                    unlink('uploads/thumbnails/course_thumbnails/optimized/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg');
                }
            }else{
                if (file_exists('uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg')) {
                    rename('uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$previous_last_modified. '.jpg', 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id .$data['last_modified']. '.jpg');
                }
            }
        }

        $this->db->where('id', $course_id);
        $this->db->update('course', $data);

        

        if ($data['status'] == 'active') {
            $this->session->set_flashdata('flash_message', get_phrase('course_updated_successfully'));
        } elseif ($data['status'] == 'pending') {
            $this->session->set_flashdata('flash_message', get_phrase('course_updated_successfully') . '. ' . get_phrase('please_wait_untill_Admin_approves_it'));
        } elseif ($data['status'] == 'draft') {
            $this->session->set_flashdata('flash_message', get_phrase('your_course_has_been_added_to_draft'));
        }
    }

    public function change_course_status($status = "", $course_id = "")
    {
        if ($status == 'active') {
            if ($this->session->userdata('admin_login') != true) {
                redirect(site_url('login'), 'refresh');
            }
        }
        $updater = array(
            'status' => $status
        );
        $this->db->where('id', $course_id);
        $this->db->update('course', $updater);
    }

    function get_course_thumbnail_url($course_id, $type = 'course_thumbnail')
    {
        // Course media placeholder is coming from the theme config file. Which has all the placehoder for different images. Choose like course type.
        $course_media_placeholders = themeConfiguration(get_frontend_settings('theme'), 'course_media_placeholders');

        $last_modified = $this->get_course_by_id($course_id)->row('last_modified');

        if (file_exists('uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id.$last_modified . '.jpg')) {
            return base_url() . 'uploads/thumbnails/course_thumbnails/optimized/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id.$last_modified . '.jpg';
        } elseif(file_exists('uploads/thumbnails/course_thumbnails/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id.$last_modified . '.jpg')) {

            //resizeImage
            resizeImage('uploads/thumbnails/course_thumbnails/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id.$last_modified . '.jpg', 'uploads/thumbnails/course_thumbnails/optimized/', 400);

            return base_url() . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id.$last_modified . '.jpg';
        }else{
            return base_url() . $course_media_placeholders[$type . '_placeholder'];
        }
    }
    public function get_lesson_thumbnail_url($lesson_id)
    {

        if (file_exists('uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg'))
            return base_url() . 'uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg';
        else
            return base_url() . 'uploads/thumbnails/thumbnail.png';
    }

    public function get_my_courses_by_category_id($category_id)
    {
        $this->db->select('course_id');
        $course_lists_by_enrol = $this->db->get_where('enrol', array('user_id' => $this->session->userdata('user_id')))->result_array();
        $course_ids = array();
        foreach ($course_lists_by_enrol as $row) {
            if (!in_array($row['course_id'], $course_ids)) {
                array_push($course_ids, $row['course_id']);
            }
        }
        $this->db->where_in('id', $course_ids);
        $this->db->where('category_id', $category_id);
        return $this->db->get('course');
    }

    public function get_my_courses_by_search_string($search_string)
    {
        $this->db->select('course_id');
        $course_lists_by_enrol = $this->db->get_where('enrol', array('user_id' => $this->session->userdata('user_id')))->result_array();
        $course_ids = array();
        foreach ($course_lists_by_enrol as $row) {
            if (!in_array($row['course_id'], $course_ids)) {
                array_push($course_ids, $row['course_id']);
            }
        }
        $this->db->where_in('id', $course_ids);
        $this->db->like('title', $search_string);
        return $this->db->get('course');
    }

    public function get_courses_by_search_string($search_string = "", $per_page = "", $uri_segment = "")
    {
        if (!addon_status('scorm_course')) {
            $this->db->where('course_type', 'general');
        }
        $this->db->where('status', 'active');

        $this->db->group_start();
            $this->db->like('title', $search_string);
            $this->db->or_like('short_description', $search_string);
            $this->db->or_like('description', $search_string);
            $this->db->or_like('outcomes', $search_string);
            $this->db->or_like('language', $search_string);
            $this->db->or_like('requirements', $search_string);
            $this->db->or_like('meta_keywords', $search_string);
            $this->db->or_like('meta_description', $search_string);
        $this->db->group_end();

        if ($per_page != "" || $uri_segment != "") {
            return $this->db->get('course', $per_page, $uri_segment);
        } else {
            return $this->db->get('course');
        }
    }


    public function get_course_by_id($course_id = "")
    {
        return $this->db->get_where('course', array('id' => $course_id));
    }

    public function delete_course($course_id = "")
    {
        $course_type = $this->get_course_by_id($course_id)->row('course_type');

        $this->db->where('id', $course_id);
        $this->db->delete('course');

        // DELETE ALL THE enrolment OF THIS COURSE FROM enrol TABLE
        $this->db->where('course_id', $course_id);
        $this->db->delete('enrol');

        if ($course_type == 'general') {
            // DELETE ALL THE LESSONS OF THIS COURSE FROM LESSON TABLE
            $lesson_checker = array('course_id' => $course_id);
            $this->db->delete('lesson', $lesson_checker);

            // DELETE ALL THE section OF THIS COURSE FROM section TABLE
            $this->db->where('course_id', $course_id);
            $this->db->delete('section');
        } elseif ($course_type == 'scorm') {
            $this->load->model('addons/scorm_model');
            $scorm_query = $this->scorm_model->get_scorm_curriculum_by_course_id($course_id);

            $this->db->where('course_id', $course_id);
            $this->db->delete('scorm_curriculum');

            if ($scorm_query->num_rows() > 0) {
                //deleted previews course directory
                $this->scorm_model->deleteDir('uploads/scorm/courses/' . $scorm_query->row('identifier'));
            }
        }
    }

    function get_top_categories($limit = "10", $category_column = "category_id")
    {
        $query = $this->db
            ->select($category_column . ", count(*) AS course_number", false)
            ->from("course")
            ->group_by($category_column)
            ->order_by("course_number", "DESC")
            ->where('status', 'active')
            ->limit($limit)
            ->get();
        return $query->result_array();
    }

    public function get_top_courses()
    {
        if (addon_status('scorm_course')) {
            return $this->db->get_where('course', array('is_top_course' => 1, 'status' => 'active'));
        } else {
            return $this->db->get_where('course', array('is_top_course' => 1, 'status' => 'active', 'course_type' => 'general'));
        }
    }

    public function get_default_category_id()
    {
        $categories = $this->get_categories()->result_array();
        foreach ($categories as $category) {
            return $category['id'];
        }
    }

    public function get_courses_by_user_id($param1 = "")
    {
        $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($param1);

        $this->db->where('status', 'draft');
        $this->db->where('user_id', $param1);
        if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
            $this->db->or_where_in('id', $multi_instructor_course_ids);
        }
        $courses['draft'] = $this->db->get('course');

        $this->db->where('status', 'pending');
        $this->db->where('user_id', $param1);
        if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
            $this->db->or_where_in('id', $multi_instructor_course_ids);
        }
        $courses['pending'] = $this->db->get('course');

        $this->db->where('status', 'active');
        $this->db->where('user_id', $param1);
        if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
            $this->db->or_where_in('id', $multi_instructor_course_ids);
        }
        $courses['active'] = $this->db->get('course');

        return $courses;
    }

    public function get_status_wise_courses($status = "")
    {
        if (addon_status('scorm_course')) {
            if ($status != "") {
                $courses = $this->db->get_where('course', array('status' => $status));
            } else {
                $courses['draft'] = $this->db->get_where('course', array('status' => 'draft'));
                $courses['pending'] = $this->db->get_where('course', array('status' => 'pending'));
                $courses['private'] = $this->db->get_where('course', array('status' => 'private'));
                $courses['active'] = $this->db->get_where('course', array('status' => 'active'));
            }
        } else {
            if ($status != "") {
                $courses = $this->db->get_where('course', array('status' => $status, 'course_type' => 'general'));
            } else {
                $courses['draft'] = $this->db->get_where('course', array('status' => 'draft', 'course_type' => 'general'));
                $courses['pending'] = $this->db->get_where('course', array('status' => 'pending', 'course_type' => 'general'));
                $courses['active'] = $this->db->get_where('course', array('status' => 'active', 'course_type' => 'general'));
                $courses['private'] = $this->db->get_where('course', array('status' => 'private', 'course_type' => 'general'));
            }
        }
        return $courses;
    }

    public function get_status_wise_courses_for_instructor($status = "")
    {
        $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($this->session->userdata('user_id'));

        if ($status != "") {
            $this->db->where('status', $status);
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->where_in('id', $multi_instructor_course_ids);
            } else {
                $this->db->where('creator', $this->session->userdata('user_id'));
            }
            $courses = $this->db->get('course');
        } else {
            $this->db->where('status', 'draft');
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->or_where_in('id', $multi_instructor_course_ids);
            } else {
                $this->db->where('creator', $this->session->userdata('user_id'));
            }
            $courses['draft'] = $this->db->get('course');

            $this->db->where('status', 'draft');
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->or_where_in('id', $multi_instructor_course_ids);
            } else {
                $this->db->where('creator', $this->session->userdata('user_id'));
            }
            $courses['pending'] = $this->db->get('course');

            $this->db->where('status', 'draft');
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->or_where_in('id', $multi_instructor_course_ids);
            } else {
                $this->db->where('creator', $this->session->userdata('user_id'));
            }
            $courses['active'] = $this->db->get('course');
        }
        return $courses;
    }

    public function get_default_sub_category_id($default_cateegory_id)
    {
        $sub_categories = $this->get_sub_categories($default_cateegory_id);
        foreach ($sub_categories as $sub_category) {
            return $sub_category['id'];
        }
    }

    public function get_instructor_wise_courses($instructor_id = "", $return_as = "")
    {
        // GET COURSE IDS FOR MULTI INSTRUCTOR
        $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($instructor_id);

        $this->db->where('user_id', $instructor_id);

        if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
            $this->db->or_where_in('id', $multi_instructor_course_ids);
        }

        $courses = $this->db->get('course');
        if ($return_as == 'simple_array') {
            $array = array();
            foreach ($courses->result_array() as $course) {
                if (!in_array($course['id'], $array)) {
                    array_push($array, $course['id']);
                }
            }
            return $array;
        } else {
            return $courses;
        }
    }

    public function get_instructor_wise_payment_history($instructor_id = "")
    {
        $courses = $this->get_instructor_wise_courses($instructor_id, 'simple_array');
        if (sizeof($courses) > 0) {
            $this->db->where_in('course_id', $courses);
            return $this->db->get('payment')->result_array();
        } else {
            return array();
        }
    }

    public function add_section($course_id)
    {
        $data['title'] = html_escape($this->input->post('title'));
        $data['course_id'] = $course_id;
        $this->db->insert('section', $data);
        $section_id = $this->db->insert_id();

        $course_details = $this->get_course_by_id($course_id)->row_array();
        $previous_sections = json_decode($course_details['section']);

        if (sizeof($previous_sections) > 0) {
            array_push($previous_sections, $section_id);
            $updater['section'] = json_encode($previous_sections);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        } else {
            $previous_sections = array();
            array_push($previous_sections, $section_id);
            $updater['section'] = json_encode($previous_sections);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        }
    }

    public function edit_section($section_id)
    {
        $data['title'] = $this->input->post('title');
        $this->db->where('id', $section_id);
        $this->db->update('section', $data);
    }

    public function delete_section($course_id, $section_id)
    {
        $this->db->where('id', $section_id);
        $this->db->delete('section');

        $this->db->where('section_id', $section_id);
        $this->db->delete('lesson');



        $course_details = $this->get_course_by_id($course_id)->row_array();
        $previous_sections = json_decode($course_details['section']);

        if (sizeof($previous_sections) > 0) {
            $new_section = array();
            for ($i = 0; $i < sizeof($previous_sections); $i++) {
                if ($previous_sections[$i] != $section_id) {
                    array_push($new_section, $previous_sections[$i]);
                }
            }
            $updater['section'] = json_encode($new_section);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        }
    }

    public function get_section($type_by, $id)
    {
        $this->db->order_by("order", "asc");
        if ($type_by == 'course') {
            return $this->db->get_where('section', array('course_id' => $id));
        } elseif ($type_by == 'section') {
            return $this->db->get_where('section', array('id' => $id));
        }
    }

    public function serialize_section($course_id, $serialization)
    {
        $updater = array(
            'section' => $serialization
        );
        $this->db->where('id', $course_id);
        $this->db->update('course', $updater);
    }

    public function add_lesson()
    {

        $data['course_id'] = html_escape($this->input->post('course_id'));
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $lesson_type_array = explode('-', $this->input->post('lesson_type'));

        $lesson_type = $lesson_type_array[0];
        $data['lesson_type'] = $lesson_type;

        $attachment_type = $lesson_type_array[1];
        $data['attachment_type'] = $attachment_type;

        if ($lesson_type == 'video') {
            // This portion is for web application's video lesson
            $lesson_provider = $this->input->post('lesson_provider');
            if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
                if ($this->input->post('video_url') == "" || $this->input->post('duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('video_url'));

                $duration_formatter = explode(':', $this->input->post('duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;

                $video_details = $this->video_model->getVideoDetails($data['video_url']);
                $data['video_type'] = $video_details['provider'];
            } elseif ($lesson_provider == 'academy_cloud') {
                if (isset($_FILES['cloud_video_file']['name']) && $_FILES['cloud_video_file']['name'] == "") {
                    return json_encode(['error' => get_phrase('invalid_video_file')]);
                }

                if ($this->input->post('academy_cloud_video_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_duration')]);
                }


                $video_upload = $this->academy_cloud_model->upload_video();
                if($video_upload['success'] == false){
                    return json_encode(['error' => $video_upload['message']]);
                }

                $data['cloud_video_id'] = $video_upload['id'];

                $duration_formatter = explode(':', $this->input->post('academy_cloud_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'academy_cloud';
            } elseif ($lesson_provider == 'html5') {
                if ($this->input->post('html5_video_url') == "" || $this->input->post('html5_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('html5_video_url'));
                $duration_formatter = explode(':', $this->input->post('html5_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'html5';
            } elseif ($lesson_provider == 'google_drive') {
                if ($this->input->post('google_drive_video_url') == "" || $this->input->post('google_drive_video_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('google_drive_video_url'));
                $duration_formatter = explode(':', $this->input->post('google_drive_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'google_drive';
            } else {
                return json_encode(['error' => get_phrase('invalid_lesson_provider')]);
            }

            // This portion is for mobile application video lessons
            if ($this->input->post('html5_video_url_for_mobile_application') == "" || $this->input->post('html5_duration_for_mobile_application') == "") {
                $mobile_app_lesson_url = "https://www.html5rocks.com/en/tutorials/video/basics/devstories.webm";
                $mobile_app_lesson_duration = "00:01:10";
            } else {
                $mobile_app_lesson_url = $this->input->post('html5_video_url_for_mobile_application');
                $mobile_app_lesson_duration = $this->input->post('html5_duration_for_mobile_application');
            }
            $duration_for_mobile_application_formatter = explode(':', $mobile_app_lesson_duration);
            $hour = sprintf('%02d', $duration_for_mobile_application_formatter[0]);
            $min  = sprintf('%02d', $duration_for_mobile_application_formatter[1]);
            $sec  = sprintf('%02d', $duration_for_mobile_application_formatter[2]);
            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = 'html5';
            $data['video_url_for_mobile_application'] = $mobile_app_lesson_url;
        } elseif ($lesson_type == "s3") {
            // SET MAXIMUM EXECUTION TIME 600
            ini_set('max_execution_time', '600');

            $fileName           = $_FILES['video_file_for_amazon_s3']['name'];
            $tmp                = explode('.', $fileName);
            $fileExtension      = strtoupper(end($tmp));

            $video_extensions = ['WEBM', 'MP4'];
            if (!in_array($fileExtension, $video_extensions)) {
                return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
            }

            if ($this->input->post('amazon_s3_duration') == "") {
                return json_encode(['error' => get_phrase('invalid_lesson_duration')]);
            }

            $upload_loaction = get_settings('video_upload_location');
            $access_key = get_settings('amazon_s3_access_key');
            $secret_key = get_settings('amazon_s3_secret_key');
            $bucket = get_settings('amazon_s3_bucket_name');
            $region = get_settings('amazon_s3_region_name');

            $s3config = array(
                'region'  => $region,
                'version' => 'latest',
                'credentials' => [
                    'key'    => $access_key, //Put key here
                    'secret' => $secret_key // Put Secret here
                ]
            );


            $tmpfile = $_FILES['video_file_for_amazon_s3'];

            $s3 = new Aws\S3\S3Client($s3config);
            $key = str_replace(".", "-" . rand(1, 9999) . ".", $tmpfile['name']);

            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $key,
                'SourceFile' => $tmpfile['tmp_name'],
                'ACL'   => 'public-read'
            ]);

            $data['video_url'] = $result['ObjectURL'];
            $data['video_type'] = 'amazon';
            $data['lesson_type'] = 'video';
            $data['attachment_type'] = 'file';

            $duration_formatter = explode(':', $this->input->post('amazon_s3_duration'));
            $hour = sprintf('%02d', $duration_formatter[0]);
            $min = sprintf('%02d', $duration_formatter[1]);
            $sec = sprintf('%02d', $duration_formatter[2]);
            $data['duration'] = $hour . ':' . $min . ':' . $sec;

            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = "html5";
            $data['video_url_for_mobile_application'] = $result['ObjectURL'];
        } elseif ($lesson_type == "system") {
            // SET MAXIMUM EXECUTION TIME 600
            ini_set('max_execution_time', '600');

            $fileName           = $_FILES['system_video_file']['name'];

            // CHECKING IF THE FILE IS AVAILABLE AND FILE SIZE IS VALID
            if (array_key_exists('system_video_file', $_FILES)) {
                if ($_FILES['system_video_file']['error'] !== UPLOAD_ERR_OK) {
                    $error_code = $_FILES['system_video_file']['error'];
                    return json_encode(['error' => phpFileUploadErrors($error_code)]);
                }
            } else {
                return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
            };

            $tmp                = explode('.', $fileName);
            $fileExtension      = strtoupper(end($tmp));

            $video_extensions = ['WEBM', 'MP4'];

            if (!in_array($fileExtension, $video_extensions)) {
                return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
            }

            // custom random name of the video file
            $uploadable_video_file    =  md5(uniqid(rand(), true)) . '.' . strtolower($fileExtension);

            if ($this->input->post('system_video_file_duration') == "") {
                return json_encode(['error' => get_phrase('invalid_lesson_duration')]);
            }


            $tmp_video_file = $_FILES['system_video_file']['tmp_name'];

            if (!file_exists('uploads/lesson_files/videos')) {
                mkdir('uploads/lesson_files/videos', 0777, true);
            }
            $video_file_path = 'uploads/lesson_files/videos/' . $uploadable_video_file;
            move_uploaded_file($tmp_video_file, $video_file_path);
            $data['video_url'] = site_url($video_file_path);
            $data['video_type'] = 'system';
            $data['lesson_type'] = 'video';
            $data['attachment_type'] = 'file';

            $duration_formatter = explode(':', $this->input->post('system_video_file_duration'));
            $hour = sprintf('%02d', $duration_formatter[0]);
            $min = sprintf('%02d', $duration_formatter[1]);
            $sec = sprintf('%02d', $duration_formatter[2]);
            $data['duration'] = $hour . ':' . $min . ':' . $sec;

            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = "html5";
            $data['video_url_for_mobile_application'] = site_url($video_file_path);
        } elseif ($lesson_type == 'text' && $attachment_type == 'description') {
            $data['attachment'] = htmlspecialchars(remove_js($this->input->post('text_description', false)));
        } else {
            if ($attachment_type == 'iframe') {
                if (empty($this->input->post('iframe_source'))) {
                    return json_encode(['error' => get_phrase('invalid_source')]);
                }
                $data['attachment'] = $this->input->post('iframe_source');
            } else {
                if ($_FILES['attachment']['name'] == "") {
                    return json_encode(['error' => get_phrase('invalid_attachment')]);
                } else {
                    $fileName           = $_FILES['attachment']['name'];
                    $tmp                = explode('.', $fileName);
                    $fileExtension      = end($tmp);
                    $uploadable_file    =  md5(uniqid(rand(), true)) . '.' . $fileExtension;
                    $data['attachment'] = $uploadable_file;

                    if (!file_exists('uploads/lesson_files')) {
                        mkdir('uploads/lesson_files', 0777, true);
                    }
                    move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
                }
            }
        }

        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = htmlspecialchars(remove_js($this->input->post('summary', false)));
        $data['is_free'] = htmlspecialchars($this->input->post('free_lesson'));


        //video caption
        if(isset($_FILES['caption']) && !empty($_FILES['caption']['name'])){
            $data['caption'] = random(15).'.vtt';
            move_uploaded_file($_FILES['caption']['tmp_name'], 'uploads/lesson_files/captions/' . $data['caption']);
        }


        $this->db->insert('lesson', $data);
        $inserted_id = $this->db->insert_id();

        if (isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != "") {
            if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
                mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
            }
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $inserted_id . '.jpg');
        }

        $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_added_successfully'));
        return json_encode(['reload' => true]);
    }

    public function edit_lesson($lesson_id)
    {

        $previous_data = $this->db->get_where('lesson', array('id' => $lesson_id))->row_array();

        $data['course_id'] = html_escape($this->input->post('course_id'));
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $lesson_type_array = explode('-', $this->input->post('lesson_type'));

        $lesson_type = $lesson_type_array[0];
        $data['lesson_type'] = $lesson_type;

        $attachment_type = $lesson_type_array[1];
        $data['attachment_type'] = $attachment_type;

        if ($lesson_type == 'video') {
            $lesson_provider = $this->input->post('lesson_provider');
            if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
                if ($this->input->post('video_url') == "" || $this->input->post('duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('video_url'));

                $duration_formatter = explode(':', $this->input->post('duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;

                $video_details = $this->video_model->getVideoDetails($data['video_url']);
                $data['video_type'] = $video_details['provider'];
            } elseif ($lesson_provider == 'academy_cloud') {
                if (isset($_FILES['cloud_video_file']['name']) && $_FILES['cloud_video_file']['name'] == "") {
                    return json_encode(['error' => get_phrase('invalid_video_file')]);
                }

                if ($this->input->post('academy_cloud_video_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_duration')]);
                }


                $video_upload = $this->academy_cloud_model->update_video($previous_data['cloud_video_id']);
                if($video_upload['success'] == false){
                    return json_encode(['error' => $video_upload['message']]);
                }

                $data['cloud_video_id'] = $video_upload['id'];

                $duration_formatter = explode(':', $this->input->post('academy_cloud_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'academy_cloud';
            } elseif ($lesson_provider == 'html5') {
                if ($this->input->post('html5_video_url') == "" || $this->input->post('html5_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('html5_video_url'));

                $duration_formatter = explode(':', $this->input->post('html5_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'html5';
            } elseif ($lesson_provider == 'google_drive') {
                if ($this->input->post('google_drive_video_url') == "" || $this->input->post('google_drive_video_duration') == "") {
                    return json_encode(['error' => get_phrase('invalid_lesson_url_and_duration')]);
                }
                $data['video_url'] = html_escape($this->input->post('google_drive_video_url'));
                $duration_formatter = explode(':', $this->input->post('google_drive_video_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'google_drive';
            } else {
                return json_encode(['error' => get_phrase('invalid_lesson_provider')]);
            }
            $data['attachment'] = "";

            // This portion is for mobile application video lessons
            if ($this->input->post('html5_video_url_for_mobile_application') == "" || $this->input->post('html5_duration_for_mobile_application') == "") {
                $mobile_app_lesson_url = "https://www.html5rocks.com/en/tutorials/video/basics/devstories.webm";
                $mobile_app_lesson_duration = "00:01:10";
            } else {
                $mobile_app_lesson_url = $this->input->post('html5_video_url_for_mobile_application');
                $mobile_app_lesson_duration = $this->input->post('html5_duration_for_mobile_application');
            }
            $duration_for_mobile_application_formatter = explode(':', $mobile_app_lesson_duration);
            $hour = sprintf('%02d', $duration_for_mobile_application_formatter[0]);
            $min  = sprintf('%02d', $duration_for_mobile_application_formatter[1]);
            $sec  = sprintf('%02d', $duration_for_mobile_application_formatter[2]);
            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = 'html5';
            $data['video_url_for_mobile_application'] = $mobile_app_lesson_url;
        } elseif ($lesson_type == "s3") {
            // SET MAXIMUM EXECUTION TIME 600
            ini_set('max_execution_time', '600');

            if (isset($_FILES['video_file_for_amazon_s3']) && !empty($_FILES['video_file_for_amazon_s3']['name'])) {
                $fileName           = $_FILES['video_file_for_amazon_s3']['name'];
                $tmp                = explode('.', $fileName);
                $fileExtension      = strtoupper(end($tmp));

                $video_extensions = ['WEBM', 'MP4'];
                if (!in_array($fileExtension, $video_extensions)) {
                    return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
                }

                $upload_loaction = get_settings('video_upload_location');
                $access_key = get_settings('amazon_s3_access_key');
                $secret_key = get_settings('amazon_s3_secret_key');
                $bucket = get_settings('amazon_s3_bucket_name');
                $region = get_settings('amazon_s3_region_name');

                $s3config = array(
                    'region'  => $region,
                    'version' => 'latest',
                    'credentials' => [
                        'key'    => $access_key, //Put key here
                        'secret' => $secret_key // Put Secret here
                    ]
                );


                $tmpfile = $_FILES['video_file_for_amazon_s3'];

                $s3 = new Aws\S3\S3Client($s3config);
                $key = str_replace(".", "-" . rand(1, 9999) . ".", preg_replace('/\s+/', '', $tmpfile['name']));

                $result = $s3->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'SourceFile' => $tmpfile['tmp_name'],
                    'ACL'   => 'public-read'
                ]);

                $data['video_url'] = $result['ObjectURL'];
                $data['video_url_for_mobile_application'] = $result['ObjectURL'];
            }

            $data['video_type'] = 'amazon';
            $data['lesson_type'] = 'video';
            $data['attachment_type'] = 'file';


            if ($this->input->post('amazon_s3_duration') == "") {
                return json_encode(['error' => get_phrase('invalid_lesson_duration')]);
            }

            $duration_formatter = explode(':', $this->input->post('amazon_s3_duration'));
            $hour = sprintf('%02d', $duration_formatter[0]);
            $min = sprintf('%02d', $duration_formatter[1]);
            $sec = sprintf('%02d', $duration_formatter[2]);
            $data['duration'] = $hour . ':' . $min . ':' . $sec;

            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = "html5";
        } elseif ($lesson_type == "system") {
            // SET MAXIMUM EXECUTION TIME 600
            ini_set('max_execution_time', '600');

            if (isset($_FILES['system_video_file']) && !empty($_FILES['system_video_file']['name'])) {

                $fileName           = $_FILES['system_video_file']['name'];

                // CHECKING IF THE FILE IS AVAILABLE AND FILE SIZE IS VALID
                if (array_key_exists('system_video_file', $_FILES)) {
                    if ($_FILES['system_video_file']['error'] !== UPLOAD_ERR_OK) {
                        $error_code = $_FILES['system_video_file']['error'];
                        return json_encode(['error' => phpFileUploadErrors($error_code)]);
                    }
                } else {
                    return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
                };

                $tmp                = explode('.', $fileName);
                $fileExtension      = strtoupper(end($tmp));

                $video_extensions = ['WEBM', 'MP4'];
                if (!in_array($fileExtension, $video_extensions)) {
                    return json_encode(['error' => get_phrase('please_select_valid_video_file')]);
                }

                // custom random name of the video file
                $uploadable_video_file    =  md5(uniqid(rand(), true)) . '.' . strtolower($fileExtension);


                $tmp_video_file = $_FILES['system_video_file']['tmp_name'];

                if (!file_exists('uploads/lesson_files/videos')) {
                    mkdir('uploads/lesson_files/videos', 0777, true);
                }
                $video_file_path = 'uploads/lesson_files/videos/' . $uploadable_video_file;
                move_uploaded_file($tmp_video_file, $video_file_path);

                $data['video_url'] = site_url($video_file_path);
                $data['video_url_for_mobile_application'] = site_url($video_file_path);

                //delete previews video
                $previews_video_url = $this->db->get_where('lesson', array('id' => $lesson_id))->row('video_url');
                $video_file = explode('/', $previews_video_url);
                unlink('uploads/lesson_files/videos/' . end($video_file));
                //end delete previews video
            }

            $data['video_type'] = 'system';
            $data['lesson_type'] = 'video';
            $data['attachment_type'] = 'file';


            if ($this->input->post('system_video_file_duration') == "") {
                return json_encode(['error' => get_phrase('invalid_lesson_duration')]);
            }

            $duration_formatter = explode(':', $this->input->post('system_video_file_duration'));
            $hour = sprintf('%02d', $duration_formatter[0]);
            $min = sprintf('%02d', $duration_formatter[1]);
            $sec = sprintf('%02d', $duration_formatter[2]);
            $data['duration'] = $hour . ':' . $min . ':' . $sec;

            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = "html5";
        } elseif ($lesson_type == 'text' && $attachment_type == 'description') {
            $data['attachment'] = htmlspecialchars(remove_js($this->input->post('text_description', false)));
        } else {
            if ($attachment_type == 'iframe') {
                if (empty($this->input->post('iframe_source'))) {
                    return json_encode(['error' => get_phrase('invalid_source')]);
                }
                $data['attachment'] = $this->input->post('iframe_source');
            } else {
                if ($_FILES['attachment']['name'] != "") {
                    // unlinking previous attachments
                    if ($previous_data['attachment'] != "") {
                        unlink('uploads/lesson_files/' . $previous_data['attachment']);
                    }

                    $fileName           = $_FILES['attachment']['name'];
                    $tmp                = explode('.', $fileName);
                    $fileExtension      = end($tmp);
                    $uploadable_file    =  md5(uniqid(rand(), true)) . '.' . $fileExtension;
                    $data['attachment'] = $uploadable_file;
                    $data['video_type'] = "";
                    $data['duration'] = "";
                    $data['video_url'] = "";
                    $data['duration_for_mobile_application'] = "";
                    $data['video_type_for_mobile_application'] = '';
                    $data['video_url_for_mobile_application'] = "";
                    if (!file_exists('uploads/lesson_files')) {
                        mkdir('uploads/lesson_files', 0777, true);
                    }
                    move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
                }
            }
        }

        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = htmlspecialchars(remove_js($this->input->post('summary', false)));
        $data['is_free'] = htmlspecialchars($this->input->post('free_lesson'));


        //video caption
        if(isset($_FILES['caption']) && !empty($_FILES['caption']['name'])){
            $data['caption'] = random(15).'.vtt';
            move_uploaded_file($_FILES['caption']['tmp_name'], 'uploads/lesson_files/captions/' . $data['caption']);
        }


        $this->db->where('id', $lesson_id);
        $this->db->update('lesson', $data);


        if (isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != "") {
            if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
                mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
            }
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg');
        }

        $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_updated_successfully'));
        return json_encode(['reload' => true]);
    }

    public function delete_lesson($lesson_id)
    {
        $user_id = $this->session->userdata('user_id');
        $previous_data = $this->db->get_where('lesson', array('id' => $lesson_id))->row_array();

        if($previous_data['video_type'] == 'academy_cloud'){
            $this->academy_cloud_model->delete_cloud_video($previous_data['cloud_video_id']);
        }

        if($previous_data['lesson_type'] == 'video' && $previous_data['attachment_type'] == 'file' && $previous_data['video_type'] == 'system'){
            unlink(str_replace(base_url(), '', $previous_data['video_url']));
        }


        //update watch histories data
        $watch_history = $this->db->get_where('watch_histories', array('student_id' => $user_id, 'course_id' => $previous_data['course_id']));
        if($watch_history->num_rows() > 0){
            $data = array();
            if($watch_history->row('watching_lesson_id') == $lesson_id){
                $data['watching_lesson_id'] = null;
            }

            $completed_lesson_arr = json_decode($watch_history->row('completed_lesson'), true);

            if (is_array($completed_lesson_arr) && count($completed_lesson_arr) > 0 && ($key = array_search($lesson_id, $completed_lesson_arr)) !== false) {
                unset($completed_lesson_arr[$key]);
                $data['completed_lesson'] = json_encode($completed_lesson_arr);
            }

            if(count($data) > 0){
                $this->db->where('student_id', $user_id);
                $this->db->where('course_id', $previous_data['course_id']);
                $this->db->update('watch_histories', $data);
            }

        }

        $this->db->where('id', $lesson_id);
        $this->db->delete('lesson');
    }

    public function update_frontend_settings()
    {
        $data['value'] = html_escape($this->input->post('banner_title'));
        $this->db->where('key', 'banner_title');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('banner_sub_title'));
        $this->db->where('key', 'banner_sub_title');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('cookie_status'));
        $this->db->where('key', 'cookie_status');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('cookie_note');
        $this->db->where('key', 'cookie_note');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('cookie_policy');
        $this->db->where('key', 'cookie_policy');
        $this->db->update('frontend_settings', $data);



        $data['value'] = $this->input->post('facebook');
        $this->db->where('key', 'facebook');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('twitter');
        $this->db->where('key', 'twitter');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('linkedin');
        $this->db->where('key', 'linkedin');
        $this->db->update('frontend_settings', $data);


        $data['value'] = $this->input->post('about_us');
        $this->db->where('key', 'about_us');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('terms_and_condition');
        $this->db->where('key', 'terms_and_condition');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('privacy_policy');
        $this->db->where('key', 'privacy_policy');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('refund_policy');
        $this->db->where('key', 'refund_policy');
        $this->db->update('frontend_settings', $data);
    }

    public function update_recaptcha_settings()
    {
        $data['value'] = html_escape($this->input->post('recaptcha_status'));
        $this->db->where('key', 'recaptcha_status');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('recaptcha_sitekey'));
        $this->db->where('key', 'recaptcha_sitekey');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('recaptcha_secretkey'));
        $this->db->where('key', 'recaptcha_secretkey');
        $this->db->update('frontend_settings', $data);
    }

    public function update_frontend_banner()
    {
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['name'] != "") {
            unlink('uploads/system/' . get_frontend_settings('banner_image'));
            $data['value'] = md5(rand(1000, 100000)) . '.jpg';
            $this->db->where('key', 'banner_image');
            $this->db->update('frontend_settings', $data);
            move_uploaded_file($_FILES['banner_image']['tmp_name'], 'uploads/system/' . $data['value']);
        }
    }

    public function update_light_logo()
    {
        if (isset($_FILES['light_logo']) && $_FILES['light_logo']['name'] != "") {
            unlink('uploads/system/' . get_frontend_settings('light_logo'));
            $data['value'] = md5(rand(1000, 100000)) . '.png';
            $this->db->where('key', 'light_logo');
            $this->db->update('frontend_settings', $data);
            move_uploaded_file($_FILES['light_logo']['tmp_name'], 'uploads/system/' . $data['value']);
        }
    }

    public function update_dark_logo()
    {
        if (isset($_FILES['dark_logo']) && $_FILES['dark_logo']['name'] != "") {
            unlink('uploads/system/' . get_frontend_settings('dark_logo'));
            $data['value'] = md5(rand(1000, 100000)) . '.png';
            $this->db->where('key', 'dark_logo');
            $this->db->update('frontend_settings', $data);
            move_uploaded_file($_FILES['dark_logo']['tmp_name'], 'uploads/system/' . $data['value']);
        }
    }

    public function update_small_logo()
    {
        if (isset($_FILES['small_logo']) && $_FILES['small_logo']['name'] != "") {
            unlink('uploads/system/' . get_frontend_settings('small_logo'));
            $data['value'] = md5(rand(1000, 100000)) . '.png';
            $this->db->where('key', 'small_logo');
            $this->db->update('frontend_settings', $data);
            move_uploaded_file($_FILES['small_logo']['tmp_name'], 'uploads/system/' . $data['value']);
        }
    }

    public function update_favicon()
    {
        if (isset($_FILES['favicon']) && $_FILES['favicon']['name'] != "") {
            unlink('uploads/system/' . get_frontend_settings('favicon'));
            $data['value'] = md5(rand(1000, 100000)) . '.png';
            $this->db->where('key', 'favicon');
            $this->db->update('frontend_settings', $data);
            move_uploaded_file($_FILES['favicon']['tmp_name'], 'uploads/system/' . $data['value']);
        }
        //move_uploaded_file($_FILES['favicon']['tmp_name'], 'uploads/system/favicon.png');
    }

    public function handleWishList($course_id)
    {
        $wishlists = array();
        $user_details = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        if ($user_details['wishlist'] == "") {
            array_push($wishlists, $course_id);
        } else {
            $wishlists = json_decode($user_details['wishlist']);
            if (in_array($course_id, $wishlists)) {
                $container = array();
                foreach ($wishlists as $key) {
                    if ($key != $course_id) {
                        array_push($container, $key);
                    }
                }
                $wishlists = $container;
                // $key = array_search($course_id, $wishlists);
                // unset($wishlists[$key]);
            } else {
                array_push($wishlists, $course_id);
            }
        }

        $updater['wishlist'] = json_encode($wishlists);
        $this->db->where('id', $this->session->userdata('user_id'));
        $this->db->update('users', $updater);
    }

    public function is_added_to_wishlist($course_id = "")
    {
        if ($this->session->userdata('user_login') == 1) {
            $wishlists = array();
            $user_details = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
            $wishlists = json_decode($user_details['wishlist']);
            if (in_array($course_id, $wishlists)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getWishLists($user_id = "")
    {
        if ($user_id == "") {
            $user_id = $this->session->userdata('user_id');
        }
        $user_details = $this->user_model->get_user($user_id)->row_array();
        return json_decode($user_details['wishlist']);
    }

    public function get_latest_10_course()
    {
        if (addon_status('scorm_course')) {
            $this->db->where('course_type', 'general');
        }
        $this->db->order_by("id", "desc");
        $this->db->limit('10');
        $this->db->where('status', 'active');
        return $this->db->get('course')->result_array();
    }

    public function enrol_student($user_id)
    {
        $purchased_courses = $this->session->userdata('cart_items');
        foreach ($purchased_courses as $purchased_course) {
            $data['user_id'] = $user_id;
            $data['course_id'] = $purchased_course;
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('enrol', $data);
        }
    }
    public function enrol_a_student_manually()
    {
        $courses_id = $this->input->post('course_id');
        $users_id   = $this->input->post('user_id');

        foreach($users_id as $user_id){
            $data['user_id'] = $user_id;

            foreach($courses_id as $course_id){
                $data['course_id'] = $course_id;

                if ($this->db->get_where('enrol', $data)->num_rows() == 0) {
                    $data['date_added'] = strtotime(date('D, d-M-Y'));
                    $this->db->insert('enrol', $data);
                }
            }
        }

        $this->session->set_flashdata('flash_message', get_phrase('student_has_been_enrolled'));

    }

    public function shortcut_enrol_a_student_manually()
    {
        $data['course_id'] = $this->input->post('course_id');
        $data['user_id']   = $this->input->post('user_id');
        if ($this->db->get_where('enrol', $data)->num_rows() > 0) {
            $response['status'] = 0;
            $response['message'] = get_phrase('student_has_already_been_enrolled_to_this_course');
            return json_encode($response);
        } else {
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('enrol', $data);
            $this->session->set_flashdata('flash_message', get_phrase('student_has_been_enrolled_to_that_course'));

            $response['status'] = 1;
            return json_encode($response);
        }
    }

    public function enrol_to_free_course($course_id = "", $user_id = "")
    {
        $course_details = $this->get_course_by_id($course_id)->row_array();
        if ($course_details['is_free_course'] == 1) {
            $data['course_id'] = $course_id;
            $data['user_id']   = $user_id;
            if ($this->db->get_where('enrol', $data)->num_rows() > 0) {
                $this->session->set_flashdata('error_message', get_phrase('student_has_already_been_enrolled_to_this_course'));
            } else {
                $data['date_added'] = strtotime(date('D, d-M-Y'));
                $this->db->insert('enrol', $data);
                $this->session->set_flashdata('flash_message', get_phrase('successfully_enrolled'));
            }
        } else {
            $this->session->set_flashdata('error_message', get_phrase('this_course_is_not_free_at_all'));
            redirect(site_url('home/course/' . slugify($course_details['title']) . '/' . $course_id), 'refresh');
        }
    }
    public function course_purchase($user_id, $method, $amount_paid, $param1 = "", $param2 = "")
    {
        $purchased_courses = $this->session->userdata('cart_items');
        $applied_coupon = $this->session->userdata('applied_coupon');

        foreach ($purchased_courses as $purchased_course) {

            if ($method == 'stripe') {
                //param1 transaction_id, param2 session_id for stripe
                $data['transaction_id'] = $param1;
                $data['session_id'] = $param2;
            }

            if ($method == 'razorpay') {
                //param1 payment keys
                $data['transaction_id'] = $param1;
            }

            $data['user_id'] = $user_id;
            $data['payment_type'] = $method;
            $data['course_id'] = $purchased_course;
            $course_details = $this->get_course_by_id($purchased_course)->row_array();

            if ($course_details['discount_flag'] == 1) {
                $data['amount'] = $course_details['discounted_price'];
                if(addon_status('affiliate_course')  && $this->session->userdata('course_referee') != "" && $this->session->userdata('course_reffer_id')  )
                {
                    $aff['buying_amount'] = $course_details['discounted_price']; // after discount ,he paid this price 
                    $aff['note'] = "discounted";
                }
            } else {
                $data['amount'] = $course_details['price'];
                if(addon_status('affiliate_course')  && $this->session->userdata('course_referee') != "" && $this->session->userdata('course_reffer_id'))
                {
                    $aff['buying_amount'] = $course_details['price'];
                    $aff['note'] = "actual price";
                }
            }

            // CHECK IF USER HAS APPLIED ANY COUPON CODE
            if ($applied_coupon) {
                $coupon_details = $this->get_coupon_details_by_code($applied_coupon)->row_array();
                $discount = ($data['amount'] * $coupon_details['discount_percentage']) / 100;
                $data['amount'] = $data['amount'] - $discount;
                $data['coupon'] = $applied_coupon;

                if(addon_status('affiliate_course')  && $this->session->userdata('course_referee') != "" && $this->session->userdata('course_reffer_id'))
                {
                    $aff['buying_amount'] =$data['amount'];
                    $aff['note'] = "coupon";
                }

            }

            if(addon_status('affiliate_course')  && $this->session->userdata('course_referee') != "" && $this->session->userdata('course_reffer_id'))
            {
                
                $aff['affiliate_amount']=ceil(($aff['buying_amount'] *  get_settings('affiliate_addon_percentage')) / 100);
                $data['amount']=$data['amount']-$aff['affiliate_amount'];
            }


            //TAX handling
            if(get_settings('course_selling_tax') > 0){
                $total_tax_on_courses_price = round(($data['amount']/100) * get_settings('course_selling_tax'), 2);
            }else{
                $total_tax_on_courses_price = 0;
            }
            $data['tax'] = $total_tax_on_courses_price;



            if (get_user_role('role_id', $course_details['creator']) == 1) {
                $data['admin_revenue'] = $data['amount'];
                $data['instructor_revenue'] = 0;
                $data['instructor_payment_status'] = 1;
            } else {
                if (get_settings('allow_instructor') == 1) {
                    $instructor_revenue_percentage = get_settings('instructor_revenue');
                    $data['instructor_revenue'] = ceil(($data['amount'] * $instructor_revenue_percentage) / 100);
                    $data['admin_revenue'] = $data['amount'] - $data['instructor_revenue'];
                } else {
                    $data['instructor_revenue'] = 0;
                    $data['admin_revenue'] = $data['amount'];
                }
                $data['instructor_payment_status'] = 0;
            }
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('payment', $data);

            // course_addon start
            if (addon_status('affiliate_course')) :
                if ($this->session->userdata('course_referee') != "" && $this->session->userdata('course_reffer_id')) {
                    $CI    = &get_instance();
                    $CI->load->model('addons/affiliate_course_model');
                    $last_affiliate_course_entry = $this->affiliate_course_model->last_affiliate_course_entry($data['user_id'], $this->session->userdata('course_reffer_id'));
                    $reffre_details_for_aff_table = $this->affiliate_course_model->get_user_by_their_unique_identifier($this->session->userdata('course_referee'));
                    $reffre_details = $this->affiliate_course_model->get_userby_id($reffre_details_for_aff_table['user_id']);
                    $course_affiliation['payment_id'] = $data['transaction_id'];
                    $course_affiliation['type'] = "course";
                    $course_affiliation['actual_amount'] =$aff['buying_amount'];
                    $course_affiliation['amount'] = $aff['affiliate_amount'];
                    $course_affiliation['note'] = $aff['note'];
                    $course_affiliation['course_id'] = $this->session->userdata('course_reffer_id');
                    $course_affiliation['percentage'] = get_settings('affiliate_addon_percentage');
                    $course_affiliation['payment_type'] = $method;
                    $course_affiliation['date_added'] = strtotime(date('D, d-M-Y'));
                    $course_affiliation['buyer_id'] = $data['user_id'];
                    $course_affiliation['referee_id'] = $reffre_details['id'];
                    $this->db->insert('course_affiliation', $course_affiliation);
                    $this->session->unset_userdata('course_referee');
                    $this->session->unset_userdata('course_reffer_id');

                }
            endif;
            // course_addon end 

        }
    }

    public function get_default_lesson($section_id)
    {
        $this->db->order_by('order', "asc");
        $this->db->limit(1);
        $this->db->where('section_id', $section_id);
        return $this->db->get('lesson');
    }

    public function get_courses_by_wishlists()
    {
        $wishlists = $this->getWishLists();
        if (sizeof($wishlists) > 0) {
            $this->db->where_in('id', $wishlists);
            return $this->db->get('course')->result_array();
        } else {
            return array();
        }
    }


    public function get_courses_of_wishlists_by_search_string($search_string)
    {
        $wishlists = $this->getWishLists();
        if (sizeof($wishlists) > 0) {
            $this->db->where_in('id', $wishlists);
            $this->db->like('title', $search_string);
            return $this->db->get('course')->result_array();
        } else {
            return array();
        }
    }

    public function get_total_duration_of_lesson_by_course_id($course_id)
    {
        $total_duration = 0;
        $lessons = $this->crud_model->get_lessons('course', $course_id)->result_array();
        foreach ($lessons as $lesson) {
            if ($lesson['lesson_type'] != "other" && $lesson['lesson_type'] != "text") {
                $time_array = explode(':', $lesson['duration']);
                $hour_to_seconds = $time_array[0] * 60 * 60;
                $minute_to_seconds = $time_array[1] * 60;
                $seconds = $time_array[2];
                $total_duration += $hour_to_seconds + $minute_to_seconds + $seconds;
            }
        }
        // return gmdate("H:i:s", $total_duration).' '.get_phrase('hours');
        $hours = floor($total_duration / 3600);
        $minutes = floor(($total_duration % 3600) / 60);
        $seconds = $total_duration % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) . ' ' . get_phrase('hours');
    }

    public function get_total_duration_of_lesson_by_section_id($section_id)
    {
        $total_duration = 0;
        $lessons = $this->crud_model->get_lessons('section', $section_id)->result_array();
        foreach ($lessons as $lesson) {
            if ($lesson['lesson_type'] != "other" && $lesson['lesson_type'] != "text") {
                $time_array = explode(':', $lesson['duration']);
                $hour_to_seconds = $time_array[0] * 60 * 60;
                $minute_to_seconds = $time_array[1] * 60;
                $seconds = $time_array[2];
                $total_duration += $hour_to_seconds + $minute_to_seconds + $seconds;
            }
        }
        //return gmdate("H:i:s", $total_duration).' '.get_phrase('hours');
        $hours = floor($total_duration / 3600);
        $minutes = floor(($total_duration % 3600) / 60);
        $seconds = $total_duration % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) . ' ' . get_phrase('hours');
    }

    public function rate($data)
    {
        if ($this->db->get_where('rating', array('user_id' => $data['user_id'], 'ratable_id' => $data['ratable_id'], 'ratable_type' => $data['ratable_type']))->num_rows() == 0) {
            $this->db->insert('rating', $data);
        } else {
            $checker = array('user_id' => $data['user_id'], 'ratable_id' => $data['ratable_id'], 'ratable_type' => $data['ratable_type']);
            $this->db->where($checker);
            $this->db->update('rating', $data);
        }
    }

    public function get_user_specific_rating($ratable_type = "", $ratable_id = "")
    {
        $reviews = $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'user_id' => $this->session->userdata('user_id'), 'ratable_id' => $ratable_id));
        if ($reviews->num_rows() > 0) {
            return $reviews->row_array();
        } else {
            return array('rating' => 0);
        }
    }

    public function get_ratings($ratable_type = "", $ratable_id = "", $is_sum = false)
    {
        if ($is_sum) {
            $this->db->select_sum('rating');
            return $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'ratable_id' => $ratable_id));
        } else {
            return $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'ratable_id' => $ratable_id));
        }
    }

    public function get_instructor_wise_course_ratings($instructor_id = "", $ratable_type = "", $is_sum = false)
    {
        $course_ids = $this->get_instructor_wise_courses($instructor_id, 'simple_array');
        $course_ids[] = 0;
        if ($is_sum) {
            $this->db->where('ratable_type', $ratable_type);
            $this->db->where_in('ratable_id', $course_ids);
            $this->db->select_sum('rating');
            return $this->db->get('rating');
        } else {
            $this->db->where('ratable_type', $ratable_type);
            $this->db->where_in('ratable_id', $course_ids);
            return $this->db->get('rating');
        }
    }
    public function get_percentage_of_specific_rating($rating = "", $ratable_type = "", $ratable_id = "")
    {
        $number_of_user_rated = $this->db->get_where('rating', array(
            'ratable_type' => $ratable_type,
            'ratable_id'   => $ratable_id
        ))->num_rows();

        $number_of_user_rated_the_specific_rating = $this->db->get_where('rating', array(
            'ratable_type' => $ratable_type,
            'ratable_id'   => $ratable_id,
            'rating'       => $rating
        ))->num_rows();

        //return $number_of_user_rated.' '.$number_of_user_rated_the_specific_rating;
        if ($number_of_user_rated_the_specific_rating > 0) {
            $percentage = ($number_of_user_rated_the_specific_rating / $number_of_user_rated) * 100;
        } else {
            $percentage = 0;
        }
        return floor($percentage);
    }

    ////////private message//////
    function send_new_private_message()
    {
        $message    = $this->input->post('message');
        $timestamp  = strtotime(date("Y-m-d H:i:s"));

        $receiver   = $this->input->post('receiver');
        $sender     = $this->session->userdata('user_id');

        //check if the thread between those 2 users exists, if not create new thread
        $num1 = $this->db->get_where('message_thread', array('sender' => $sender, 'receiver' => $receiver))->num_rows();
        $num2 = $this->db->get_where('message_thread', array('sender' => $receiver, 'receiver' => $sender))->num_rows();
        if ($num1 == 0 && $num2 == 0) {
            $message_thread_code                        = substr(md5(rand(100000000, 20000000000)), 0, 15);
            $data_message_thread['message_thread_code'] = $message_thread_code;
            $data_message_thread['sender']              = $sender;
            $data_message_thread['receiver']            = $receiver;
            $this->db->insert('message_thread', $data_message_thread);
        }
        if ($num1 > 0)
            $message_thread_code = $this->db->get_where('message_thread', array('sender' => $sender, 'receiver' => $receiver))->row()->message_thread_code;
        if ($num2 > 0)
            $message_thread_code = $this->db->get_where('message_thread', array('sender' => $receiver, 'receiver' => $sender))->row()->message_thread_code;


        $data_message['message_thread_code']    = $message_thread_code;
        $data_message['message']                = $message;
        $data_message['sender']                 = $sender;
        $data_message['timestamp']              = $timestamp;
        $data_message['read_status']            = 0;
        $this->db->insert('message', $data_message);

        return $message_thread_code;
    }

    function send_reply_message($message_thread_code)
    {
        $message    = html_escape($this->input->post('message'));
        $timestamp  = strtotime(date("Y-m-d H:i:s"));
        $sender     = $this->session->userdata('user_id');

        $data_message['message_thread_code']    = $message_thread_code;
        $data_message['message']                = $message;
        $data_message['sender']                 = $sender;
        $data_message['timestamp']              = $timestamp;
        $data_message['read_status']            = 0;
        $this->db->insert('message', $data_message);
    }

    function mark_thread_messages_read($message_thread_code)
    {
        // mark read only the oponnent messages of this thread, not currently logged in user's sent messages
        $current_user = $this->session->userdata('user_id');
        $this->db->where('sender !=', $current_user);
        $this->db->where('message_thread_code', $message_thread_code);
        $this->db->update('message', array('read_status' => 1));
    }

    function count_unread_message_of_thread($message_thread_code)
    {
        $unread_message_counter = 0;
        $current_user = $this->session->userdata('user_id');
        $messages = $this->db->get_where('message', array('message_thread_code' => $message_thread_code))->result_array();
        foreach ($messages as $row) {
            if ($row['sender'] != $current_user && $row['read_status'] == '0')
                $unread_message_counter++;
        }
        return $unread_message_counter;
    }

    public function get_last_message_by_message_thread_code($message_thread_code)
    {
        $this->db->order_by('message_id', 'desc');
        $this->db->limit(1);
        $this->db->where(array('message_thread_code' => $message_thread_code));
        return $this->db->get('message');
    }

    function curl_request($code = '')
    {

        $purchase_code = $code;

        $personal_token = "FkA9UyDiQT0YiKwYLK3ghyFNRVV9SeUn";
        $url = "https://api.envato.com/v3/market/author/sale?code=" . $purchase_code;
        $curl = curl_init($url);

        //setting the header for the rest of the api
        $bearer   = 'bearer ' . $personal_token;
        $header   = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:' . $purchase_code . '.json';
        $ch_verify = curl_init($verify_url . '?code=' . $purchase_code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        $response = json_decode($cinit_verify_data, true);

        if (count($response['verify-purchase']) > 0) {
            return true;
        } else {
            return false;
        }
    }


    // version 1.3
    function get_currencies()
    {
        return $this->db->get('currency')->result_array();
    }

    function get_paypal_supported_currencies()
    {
        $this->db->where('paypal_supported', 1);
        return $this->db->get('currency')->result_array();
    }

    function get_stripe_supported_currencies()
    {
        $this->db->where('stripe_supported', 1);
        return $this->db->get('currency')->result_array();
    }

    // version 1.4
    function filter_course($search_string = "", $selected_category_id = "", $selected_price = "", $selected_level = "", $selected_language = "", $selected_rating = "", $selected_sorting = "", $per_page = "", $uri_segment = "")
    {

        if ($selected_category_id != "all") {
            $category_details = $this->get_category_details_by_id($selected_category_id)->row_array();

            if ($category_details['parent'] > 0) {
                $category_type = 'sub_category';
            } else {
                $category_type = 'parent_category';
            }
        }else{
            $category_type = 'all';
        }

        $scorm_addon_status = addon_status('scorm_course');



        //START QUERY FOR RATING'S FILTER
        $course_ids = array(0);

        if ($selected_rating != "all") {
            $this->db->select('c.*');
            $this->db->select('AVG(r.rating) avg_rating', FALSE);

            if($search_string != ""){
                $this->db->group_start();
                $this->db->like('c.title', $search_string);
                $this->db->or_like('c.short_description', $search_string);
                $this->db->or_like('c.description', $search_string);
                $this->db->or_like('c.outcomes', $search_string);
                $this->db->or_like('c.language', $search_string);
                $this->db->or_like('c.requirements', $search_string);
                $this->db->or_like('c.meta_keywords', $search_string);
                $this->db->or_like('c.meta_description', $search_string);
                $this->db->group_end();
            }

            if ($category_type != "all" && $category_type == 'sub_category') {
                $this->db->where('c.sub_category_id', $selected_category_id);
            } elseif($category_type != "all" && $category_type == 'parent_category') {
                $this->db->where('c.category_id', $selected_category_id);
            }

            if ($selected_price != "all" && $selected_price == "paid") {
                $this->db->where('c.is_free_course', null);
            } elseif ($selected_price != "all" && $selected_price == "free") {
                $this->db->where('c.is_free_course', 1);
            }

            if ($selected_level != "all") {
                $this->db->where('c.level', $selected_level);
            }

            if ($selected_language != "all") {
                $this->db->where('c.language', $selected_language);
            }

            if(!$scorm_addon_status){
                $this->db->where('c.course_type', 'general');
            }

            $this->db->where('c.status', 'active');

            $this->db->from('course c')->join('rating r', 'r.ratable_id = c.id', 'left');
            $this->db->group_by('c.id');
            $this->db->order_by('avg_rating', 'desc');
            $courses = $this->db->get()->result_array();

            //for join query new code
            foreach ($courses as $course) {
                if($course['avg_rating'] <= 0) break;

                if ($course['avg_rating'] == $selected_rating) {
                    array_push($course_ids, $course['id']);
                }
            }
        }
        //END QUERY FOR RATING'S FILTER




        
        $this->db->select('c.*');
        $this->db->select('AVG(r.rating) avg_rating', FALSE);

        if($search_string != ""){
            $this->db->group_start();
            $this->db->like('c.title', $search_string);
            $this->db->or_like('c.short_description', $search_string);
            $this->db->or_like('c.description', $search_string);
            $this->db->or_like('c.outcomes', $search_string);
            $this->db->or_like('c.language', $search_string);
            $this->db->or_like('c.requirements', $search_string);
            $this->db->or_like('c.meta_keywords', $search_string);
            $this->db->or_like('c.meta_description', $search_string);
            $this->db->group_end();
        }

        if($selected_rating != "all"){
            $this->db->group_start();
            $this->db->where_in('c.id', $course_ids);
            $this->db->group_end();
        }

        if ($category_type != "all" && $category_type == 'sub_category') {
            $this->db->where('c.sub_category_id', $selected_category_id);
        } elseif($category_type != "all" && $category_type == 'parent_category') {
            $this->db->where('c.category_id', $selected_category_id);
        }

        if ($selected_price != "all" && $selected_price == "paid") {
            $this->db->where('c.is_free_course', null);
        } elseif ($selected_price != "all" && $selected_price == "free") {
            $this->db->where('c.is_free_course', 1);
        }

        if ($selected_level != "all") {
            $this->db->where('c.level', $selected_level);
        }

        if ($selected_language != "all") {
            $this->db->where('c.language', $selected_language);
        }

        if(!$scorm_addon_status){
            $this->db->where('c.course_type', 'general');
        }

        $this->db->where('c.status', 'active');


        $this->db->from('course c')->join('rating r', 'r.ratable_id = c.id', 'left');
        $this->db->group_by('c.id');

        //sorting
        if($selected_sorting != "" && $selected_sorting == 'newest'){
            $this->db->order_by('c.id', 'desc');
        }elseif($selected_sorting != "" && $selected_sorting == 'lowest-price'){
            $this->db->order_by('is_free_course ASC, price ASC, discount_flag DESC, discounted_price ASC');
        }elseif($selected_sorting != "" && $selected_sorting == 'highest-price'){
            $this->db->order_by('price DESC, discount_flag ASC, discounted_price DESC');
        }elseif($selected_sorting != "" && $selected_sorting == 'discounted'){
            $this->db->order_by('discount_flag DESC, price ASC');
        }elseif($selected_sorting != "" && $selected_sorting == 'highest-rating'){
            $this->db->order_by('avg_rating', 'desc');
        }

        //$this->db->order_by(6, 'RANDOM');

        if ($per_page != "" || $uri_segment != "") {
            $this->db->limit($per_page, $uri_segment);
            return $this->db->get();
        } else {
            return $this->db->get();
        }
    }

    public function get_courses($category_id = "", $sub_category_id = "", $instructor_id = 0)
    {
        if ($category_id > 0 && $sub_category_id > 0 && $instructor_id > 0) {

            $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($instructor_id);
            $this->db->where('category_id', $category_id);
            $this->db->where('sub_category_id', $sub_category_id);
            $this->db->where('user_id', $instructor_id);

            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->or_where_in('id', $multi_instructor_course_ids);
            }

            return $this->db->get('course');
        } elseif ($category_id > 0 && $sub_category_id > 0 && $instructor_id == 0) {
            return $this->db->get_where('course', array('category_id' => $category_id, 'sub_category_id' => $sub_category_id));
        } else {
            return $this->db->get('course');
        }
    }

    public function filter_course_for_backend($category_id, $instructor_id, $price, $status)
    {
        // MULTI INSTRUCTOR COURSE IDS
        $multi_instructor_course_ids = array();
        if ($instructor_id != "all") {
            $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($instructor_id);
        }

        if ($category_id != "all") {
            $this->db->where('sub_category_id', $category_id);
        }

        if ($price != "all") {
            if ($price == "paid") {
                $this->db->where('is_free_course', null);
            } elseif ($price == "free") {
                $this->db->where('is_free_course', 1);
            }
        }

        if ($instructor_id != "all") {
            $this->db->where('user_id', $instructor_id);
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->or_where_in('id', $multi_instructor_course_ids);
            }
        }

        if ($status != "all") {
            $this->db->where('status', $status);
        }
        return $this->db->get('course')->result_array();
    }

    public function sort_section($section_json)
    {
        $sections = json_decode($section_json);
        foreach ($sections as $key => $value) {
            $updater = array(
                'order' => $key + 1
            );
            $this->db->where('id', $value);
            $this->db->update('section', $updater);
        }
    }

    public function sort_lesson($lesson_json)
    {
        $lessons = json_decode($lesson_json);
        foreach ($lessons as $key => $value) {
            $updater = array(
                'order' => $key + 1
            );
            $this->db->where('id', $value);
            $this->db->update('lesson', $updater);
        }
    }
    public function sort_question($question_json)
    {
        $questions = json_decode($question_json);
        foreach ($questions as $key => $value) {
            $updater = array(
                'order' => $key + 1
            );
            $this->db->where('id', $value);
            $this->db->update('question', $updater);
        }
    }

    public function get_free_and_paid_courses($price_status = "", $instructor_id = "")
    {
        // MULTI INSTRUCTOR COURSE IDS
        $multi_instructor_course_ids = array();
        if ($instructor_id > 0) {
            $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($instructor_id);
        }

        $this->db->where('status', 'active');
        if ($price_status == 'free') {
            $this->db->where('is_free_course', 1);
        } else {
            $this->db->where('is_free_course', null);
        }


        if (!$this->session->userdata('admin_login')) {
            if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
                $this->db->where_in('id', $multi_instructor_course_ids);
            } else {
                $this->db->where('creator', $instructor_id);
            }
        }
        return $this->db->get('course');
    }

    // Adding quiz functionalities
    public function add_quiz($course_id = "")
    {
        $data['course_id'] = $course_id;
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $data['lesson_type'] = 'quiz';
        $data['duration'] = $this->input->post('quiz_duration');
        $data['attachment_type'] = 'json';
        $data['attachment'] = json_encode(array('total_marks' => htmlspecialchars($this->input->post('total_marks'))));
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = html_escape($this->input->post('summary'));
        $this->db->insert('lesson', $data);
    }

    // updating quiz functionalities
    public function edit_quiz($lesson_id = "")
    {
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));
        $data['duration'] = $this->input->post('quiz_duration');
        $data['attachment'] = json_encode(array('total_marks' => htmlspecialchars($this->input->post('total_marks'))));
        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = html_escape($this->input->post('summary'));
        $this->db->where('id', $lesson_id);
        $this->db->update('lesson', $data);
    }

    // Get quiz questions
    public function get_quiz_questions($quiz_id)
    {
        $this->db->order_by("order", "asc");
        $this->db->where('quiz_id', $quiz_id);
        return $this->db->get('question');
    }

    public function get_quiz_question_by_id($question_id)
    {
        $this->db->order_by("order", "asc");
        $this->db->where('id', $question_id);
        return $this->db->get('question');
    }

    // Add Quiz Questions
    public function manage_quiz_questions($quiz_id, $question_id, $action)
    {
        $question_type = $this->input->post('question_type');
        if ($question_type == 'multiple_choice' || $question_type == 'single_choice') {
            return $this->manage_mcq_choice_question($quiz_id, $question_id, $action);
        } elseif ($question_type == 'plain_text') {
            return $this->manage_plain_text_question($quiz_id, $question_id, $action);
        } elseif ($question_type == 'fill_in_the_blank') {
            return $this->manage_fill_in_the_blank_question($quiz_id, $question_id, $action);
        }
    }

    // multiple_choice_question crud functions
    function manage_mcq_choice_question($quiz_id, $question_id, $action)
    {
        if (sizeof($this->input->post('options')) != $this->input->post('number_of_options')) {
            echo get_phrase('no_options_can_be_blank_and_there_has_to_be_atleast_one_answer');
            return;
        }
        if (empty($this->input->post('title'))) {
            echo get_phrase('question_title_can_not_be_empty');
            return;
        }
        foreach ($this->input->post('options') as $option) {
            if ($option == "") {
                echo get_phrase('no_options_can_be_blank_and_there_has_to_be_atleast_one_answer');
                return;
            }
        }
        if (is_array($this->input->post('correct_answers')) && sizeof($this->input->post('correct_answers')) == 0) {
            $correct_answers = [""];
        } elseif (!empty($this->input->post('correct_answers'))) {
            $correct_answers = $this->input->post('correct_answers');
        } else {
            echo get_phrase('correct_answer_can_not_be_empty');
            return;
        }

        $data['title']              = htmlspecialchars($this->input->post('title', false));
        $data['number_of_options']  = htmlspecialchars($this->input->post('number_of_options'));
        $data['type']               = htmlspecialchars($this->input->post('question_type'));
        $data['options']            = json_encode($this->input->post('options'));
        $data['correct_answers']    = json_encode($correct_answers);

        if ($action == 'add') {
            $data['quiz_id']            = $quiz_id;
            $this->db->insert('question', $data);
        } elseif ($action == 'edit') {
            $this->db->where('id', $question_id);
            $this->db->update('question', $data);
        }
        return true;
    }

    function manage_plain_text_question($quiz_id, $question_id, $action)
    {
        $data['title']              = htmlspecialchars($this->input->post('title', false));
        $data['type']               = htmlspecialchars($this->input->post('question_type'));
        $data['number_of_options']  = 1;

        if ($action == 'add') {
            $data['quiz_id']            = $quiz_id;
            $this->db->insert('question', $data);
        } elseif ($action == 'edit') {
            $this->db->where('id', $question_id);
            $this->db->update('question', $data);
        }
        return true;
    }

    function manage_fill_in_the_blank_question($quiz_id, $question_id, $action)
    {
        if (empty($this->input->post('title'))) {
            echo get_phrase('question_title_can_not_be_empty');
            return;
        } elseif (empty($this->input->post('correct_answers'))) {
            echo get_phrase('correct_answer_can_not_be_empty');
            return;
        }

        $data['title']              = htmlspecialchars($this->input->post('title', false));
        $data['type']               = htmlspecialchars($this->input->post('question_type'));
        $data['correct_answers']    = json_encode(explode(",", $this->input->post('correct_answers')));
        $data['options']            = json_encode(array());
        $data['number_of_options']  = count(json_decode($data['correct_answers']));

        if ($action == 'add') {
            $data['quiz_id']            = $quiz_id;
            $this->db->insert('question', $data);
        } elseif ($action == 'edit') {
            $this->db->where('id', $question_id);
            $this->db->update('question', $data);
        }
        return true;
    }

    function delete_quiz_question($question_id)
    {
        $this->db->where('id', $question_id);
        $this->db->delete('question');
        return true;
    }

    function get_application_details()
    {
        $purchase_code = get_settings('purchase_code');
        $returnable_array = array(
            'purchase_code_status' => get_phrase('not_found'),
            'support_expiry_date'  => get_phrase('not_found'),
            'customer_name'        => get_phrase('not_found')
        );

        $personal_token = "gC0J1ZpY53kRpynNe4g2rWT5s4MW56Zg";
        $url = "https://api.envato.com/v3/market/author/sale?code=" . $purchase_code;
        $curl = curl_init($url);

        //setting the header for the rest of the api
        $bearer   = 'bearer ' . $personal_token;
        $header   = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:' . $purchase_code . '.json';
        $ch_verify = curl_init($verify_url . '?code=' . $purchase_code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        $response = json_decode($cinit_verify_data, true);

        if (count($response['verify-purchase']) > 0) {

            //print_r($response);
            $item_name         = $response['verify-purchase']['item_name'];
            $purchase_time       = $response['verify-purchase']['created_at'];
            $customer         = $response['verify-purchase']['buyer'];
            $licence_type       = $response['verify-purchase']['licence'];
            $support_until      = $response['verify-purchase']['supported_until'];
            $customer         = $response['verify-purchase']['buyer'];

            $purchase_date      = date("d M, Y", strtotime($purchase_time));

            $todays_timestamp     = strtotime(date("d M, Y"));
            $support_expiry_timestamp = strtotime($support_until);

            $support_expiry_date  = date("d M, Y", $support_expiry_timestamp);

            if ($todays_timestamp > $support_expiry_timestamp)
                $support_status    = 'expired';
            else
                $support_status    = 'valid';

            $returnable_array = array(
                'purchase_code_status' => $support_status,
                'support_expiry_date'  => $support_expiry_date,
                'customer_name'        => $customer,
                'product_license'      => 'valid',
                'license_type'         => $licence_type
            );
        } else {
            $returnable_array = array(
                'purchase_code_status' => 'invalid',
                'support_expiry_date'  => 'invalid',
                'customer_name'        => 'invalid',
                'product_license'      => 'invalid',
                'license_type'         => 'invalid'
            );
        }

        return $returnable_array;
    }

    // Version 2.2 codes

    // This function is responsible for retreving all the language file from language folder
    function get_all_languages()
    {
        $language_files = array();
        $all_files = $this->get_list_of_language_files();
        foreach ($all_files as $file) {
            $info = pathinfo($file);
            if (isset($info['extension']) && strtolower($info['extension']) == 'json') {
                $file_name = explode('.json', $info['basename']);
                array_push($language_files, $file_name[0]);
            }
        }
        return $language_files;
    }

    // This function is responsible for showing all the installed themes
    function get_installed_themes($dir = APPPATH . '/views/frontend')
    {
        $result = array();
        $cdir = $files = preg_grep('/^([^.])/', scandir($dir));
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    array_push($result, $value);
                }
            }
        }
        return $result;
    }
    // This function is responsible for showing all the uninstalled themes inside themes folder
    function get_uninstalled_themes($dir = 'themes')
    {
        $result = array();
        $cdir = $files = preg_grep('/^([^.])/', scandir($dir));
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", "..", ".DS_Store"))) {
                array_push($result, $value);
            }
        }
        return $result;
    }
    // This function is responsible for retreving all the language file from language folder
    function get_list_of_language_files($dir = APPPATH . '/language', &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->get_list_of_directories_and_files($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }

    // This function is responsible for retreving all the files and folder
    function get_list_of_directories_and_files($dir = APPPATH, &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->get_list_of_directories_and_files($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }

    function remove_files_and_folders($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->remove_files_and_folders($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    function get_category_wise_courses($category_id = "")
    {
        $category_details = $this->get_category_details_by_id($category_id)->row_array();

        if ($category_details['parent'] > 0) {
            $this->db->where('sub_category_id', $category_id);
        } else {
            $this->db->where('category_id', $category_id);
        }
        $this->db->where('status', 'active');
        return $this->db->get('course');
    }

    function activate_theme($theme_to_active)
    {
        $data['value'] = $theme_to_active;
        $this->db->where('key', 'theme');
        $this->db->update('frontend_settings', $data);
    }

    // code of mark this lesson as completed
    function update_watch_history_manually($lesson_id = "", $course_id = "", $user_id = "")
    {
        $is_completed = 0;
        if ($lesson_id == "") {
            $lesson_id = $this->input->post('lesson_id');
        }
        if ($course_id == "") {
            $course_id = $this->input->post('course_id');
        }
        if ($user_id == "") {
            $user_id   = $this->session->userdata('user_id');
        }
        $query = $this->db->get_where('watch_histories', array('course_id' => $course_id, 'student_id' => $user_id));
        $course_progress = $query->row('course_progress');
        if ($query->num_rows() > 0) {
            $lesson_ids = json_decode($query->row('completed_lesson'), true);
            if (!is_array($lesson_ids)) $lesson_ids = array();
            if (!in_array($lesson_id, $lesson_ids)) {
                array_push($lesson_ids, $lesson_id);
                $total_lesson = $this->db->get_where('lesson', array('course_id' => $course_id))->num_rows();
                $course_progress = (100 / $total_lesson) * count($lesson_ids);

                $this->db->where('watch_history_id', $query->row('watch_history_id'));
                $this->db->update('watch_histories', array('course_progress' => $course_progress, 'completed_lesson' => json_encode($lesson_ids), 'date_updated' => time()));
                $is_completed = 1;
            } else {
                if (($key = array_search($lesson_id, $lesson_ids)) !== false) {
                    unset($lesson_ids[$key]);
                }
                $total_lesson = $this->db->get_where('lesson', array('course_id' => $course_id))->num_rows();
                $course_progress = (100 / $total_lesson) * count($lesson_ids);

                $this->db->where('watch_history_id', $query->row('watch_history_id'));
                $this->db->update('watch_histories', array('course_progress' => $course_progress, 'completed_lesson' => json_encode($lesson_ids), 'date_updated' => time()));
                $is_completed = 0;
            }
            // CHECK IF THE USER IS ELIGIBLE FOR CERTIFICATE
            if (addon_status('certificate') && $course_progress >= 100) {
                $this->load->model('addons/Certificate_model', 'certificate_model');
                $this->certificate_model->check_certificate_eligibility($course_id, $user_id);
            }
        }else{
            $total_lesson = $this->db->get_where('lesson', array('course_id' => $course_id))->num_rows();
            $course_progress = (100 / $total_lesson);

            $insert_data['course_id'] = $course_id;
            $insert_data['student_id'] = $user_id;
            $insert_data['completed_lesson'] = json_encode(array($lesson_id));
            $insert_data['course_progress'] = $course_progress;
            $insert_data['watching_lesson_id'] = $lesson_id;
            $insert_data['date_added'] = $course_progress;
            $this->db->insert('watch_histories', $insert_data);
        }

        return json_encode(array('lesson_id' => $lesson_id, 'course_progress' => round($course_progress), 'is_completed' => $is_completed));
    }



    //FOR MOBILE
    function enrol_to_free_course_mobile($course_id = "", $user_id = "")
    {
        $data['course_id'] = $course_id;
        $data['user_id']   = $user_id;
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        if ($this->db->get_where('course', array('id' => $course_id))->row('is_free_course') == 1) :
            $this->db->insert('enrol', $data);
        endif;
    }

    function check_course_enrolled($course_id = "", $user_id = "")
    {
        return $this->db->get_where('enrol', array('course_id' => $course_id, 'user_id' => $user_id))->num_rows();
    }


    // GET PAYOUTS
    public function get_payouts($id = "", $type = "")
    {
        $this->db->order_by('id', 'DESC');
        if ($id > 0 && $type == 'user') {
            $this->db->where('user_id', $id);
        } elseif ($id > 0 && $type == 'payout') {
            $this->db->where('id', $id);
        }
        return $this->db->get('payout');
    }

    // GET COMPLETED PAYOUTS BY DATE RANGE
    public function get_completed_payouts_by_date_range($timestamp_start = "", $timestamp_end = "")
    {
        $this->db->order_by('id', 'DESC');
        $this->db->where('date_added >=', $timestamp_start);
        $this->db->where('date_added <=', $timestamp_end);
        $this->db->where('status', 1);
        return $this->db->get('payout');
    }

    // GET PENDING PAYOUTS BY DATE RANGE
    public function get_pending_payouts()
    {
        $this->db->order_by('id', 'DESC');
        $this->db->where('status', 0);
        return $this->db->get('payout');
    }

    // GET TOTAL PAYOUT AMOUNT OF AN INSTRUCTOR
    public function get_total_payout_amount($id = "")
    {
        $checker = array(
            'user_id' => $id,
            'status'  => 1
        );
        $this->db->order_by('id', 'DESC');
        $payouts = $this->db->get_where('payout', $checker)->result_array();
        $total_amount = 0;
        foreach ($payouts as $payout) {
            $total_amount = $total_amount + $payout['amount'];
        }
        return $total_amount;
    }

    // GET TOTAL REVENUE AMOUNT OF AN INSTRUCTOR
    public function get_total_revenue($id = "")
    {
        $revenues = $this->get_instructor_revenue($id);
        $total_amount = 0;
        foreach ($revenues as $key => $revenue) {
            $total_amount = $total_amount + $revenue['instructor_revenue'];
        }
        return $total_amount;
    }

    // GET TOTAL PENDING AMOUNT OF AN INSTRUCTOR
    public function get_total_pending_amount($id = "")
    {
        $total_revenue = $this->get_total_revenue($id);
        $total_payouts = $this->get_total_payout_amount($id);
        $total_pending_amount = $total_revenue - $total_payouts;
        return $total_pending_amount;
    }

    // GET REQUESTED WITHDRAWAL AMOUNT OF AN INSTRUCTOR
    public function get_requested_withdrawal_amount($id = "")
    {
        $requested_withdrawal_amount = 0;
        $checker = array(
            'user_id' => $id,
            'status' => 0
        );
        $payouts = $this->db->get_where('payout', $checker);
        if ($payouts->num_rows() > 0) {
            $payouts = $payouts->row_array();
            $requested_withdrawal_amount = $payouts['amount'];
        }
        return $requested_withdrawal_amount;
    }

    // GET REQUESTED WITHDRAWALS OF AN INSTRUCTOR
    public function get_requested_withdrawals($id = "")
    {
        $requested_withdrawal_amount = 0;
        $checker = array(
            'user_id' => $id,
            'status' => 0
        );
        $payouts = $this->db->get_where('payout', $checker);

        return $payouts;
    }

    // ADD NEW WITHDRAWAL REQUEST
    public function add_withdrawal_request()
    {
        $user_id = $this->session->userdata('user_id');
        $total_pending_amount = $this->get_total_pending_amount($user_id);

        if (addon_status('ebook')) {
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

        $requested_withdrawal_amount = $this->input->post('withdrawal_amount');
        if ($total_pending_amount > 0 && $total_pending_amount >= $requested_withdrawal_amount) {
            $data['amount']     = $requested_withdrawal_amount;
            $data['user_id']    = $this->session->userdata('user_id');
            $data['date_added'] = strtotime(date('D, d M Y'));
            $data['status']     = 0;
            $this->db->insert('payout', $data);
            $this->session->set_flashdata('flash_message', get_phrase('withdrawal_requested'));
        } else {
            $this->session->set_flashdata('error_message', get_phrase('invalid_withdrawal_amount'));
        }
    }

    // DELETE WITHDRAWAL REQUESTS
    public function delete_withdrawal_request()
    {
        $checker = array(
            'user_id' => $this->session->userdata('user_id'),
            'status' => 0
        );
        $requested_withdrawal = $this->db->get_where('payout', $checker);
        if ($requested_withdrawal->num_rows() > 0) {
            $this->db->where($checker);
            $this->db->delete('payout');
            $this->session->set_flashdata('flash_message', get_phrase('withdrawal_deleted'));
        } else {
            $this->session->set_flashdata('error_message', get_phrase('withdrawal_not_found'));
        }
    }

    // get instructor wise total enrolment. this function return the number of enrolment for a single instructor
    public function instructor_wise_enrolment($instructor_id)
    {
        $course_ids = $this->crud_model->get_instructor_wise_courses($instructor_id, 'simple_array');
        if (!count($course_ids) > 0) {
            return false;
        }
        $this->db->select('user_id');
        $this->db->where_in('course_id', $course_ids);
        return $this->db->get('enrol');
    }

    public function check_duplicate_payment_for_stripe($transaction_id = "", $stripe_session_id = "", $user_id = "")
    {
        if ($user_id == "") {
            $user_id = $this->session->userdata('user_id');
        }

        $query = $this->db->get_where('payment', array('user_id' => $user_id, 'transaction_id' => $transaction_id, 'session_id' => $stripe_session_id));
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_course_by_course_type($type = "")
    {
        if ($type != "") {
            $this->db->where('course_type', $type);
        }
        return $this->db->get('course');
    }

    public function check_recaptcha()
    {
        if (isset($_POST["g-recaptcha-response"])) {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => get_frontend_settings('recaptcha_secretkey'),
                'response' => $_POST["g-recaptcha-response"]
            );
            $query = http_build_query($data);
            $options = array(
                'http' => array(
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "Content-Length: " . strlen($query) . "\r\n" .
                        "User-Agent:MyAgent/1.0\r\n",
                    'method' => 'POST',
                    'content' => $query
                )
            );
            $context  = stream_context_create($options);
            $verify = file_get_contents($url, false, $context);
            $captcha_success = json_decode($verify);
            if ($captcha_success->success == false) {
                return false;
            } else if ($captcha_success->success == true) {
                return true;
            }
        } else {
            return false;
        }
    }

    function get_course_by_user($user_id = "", $course_type = "")
    {
        $multi_instructor_course_ids = $this->multi_instructor_course_ids_for_an_instructor($user_id);
        if ($course_type != "") {
            $this->db->where('course_type', $course_type);
        }
        $this->db->where('user_id', $user_id);

        if ($multi_instructor_course_ids && count($multi_instructor_course_ids)) {
            $this->db->or_where_in('id', $multi_instructor_course_ids);
        }

        return $this->db->get('course');
    }

    public function multi_instructor_course_ids_for_an_instructor($instructor_id)
    {
        $course_ids = array();
        $multi_instructor_courses = $this->db->like('user_id', $instructor_id)->where('multi_instructor', 1)->get('course')->result_array();
        foreach ($multi_instructor_courses as $key => $multi_instructor_course) {
            $exploded_user_ids = explode(',', $multi_instructor_course['user_id']);
            if (in_array($instructor_id, $exploded_user_ids)) {
                array_push($course_ids, $multi_instructor_course['id']);
            } elseif ($multi_instructor_course['creator'] == $instructor_id) {
                array_push($course_ids, $multi_instructor_course['id']);
            }
        }
        return $course_ids;
    }

    /** COUPONS FUNCTIONS */
    public function get_coupons($id = null)
    {
        if ($id > 0) {
            $this->db->where('id', $id);
        }
        return $this->db->get('coupons');
    }

    public function get_coupon_details_by_code($code)
    {
        $this->db->where('code', $code);
        return $this->db->get('coupons');
    }

    public function add_coupon()
    {
        if (isset($_POST['code']) && !empty($_POST['code']) && isset($_POST['discount_percentage']) && !empty($_POST['discount_percentage']) && isset($_POST['expiry_date']) && !empty($_POST['expiry_date'])) {
            $data['code'] = $this->input->post('code');
            $data['discount_percentage'] = $this->input->post('discount_percentage') > 0 ? $this->input->post('discount_percentage') : 0;
            $data['expiry_date'] = strtotime($this->input->post('expiry_date'));
            $data['created_at'] = strtotime(date('D, d-M-Y'));

            $availability = $this->db->get_where('coupons', ['code' => $data['code']])->num_rows();
            if ($availability) {
                return false;
            } else {
                $this->db->insert('coupons', $data);
                return true;
            }
        } else {
            return false;
        }
    }
    public function edit_coupon($coupon_id)
    {
        if (isset($_POST['code']) && !empty($_POST['code']) && isset($_POST['discount_percentage']) && !empty($_POST['discount_percentage']) && isset($_POST['expiry_date']) && !empty($_POST['expiry_date'])) {
            $data['code'] = $this->input->post('code');
            $data['discount_percentage'] = $this->input->post('discount_percentage') > 0 ? $this->input->post('discount_percentage') : 0;
            $data['expiry_date'] = strtotime($this->input->post('expiry_date'));
            $data['created_at'] = strtotime(date('D, d-M-Y'));

            $this->db->where('id !=', $coupon_id);
            $this->db->where('code', $data['code']);
            $availability = $this->db->get('coupons')->num_rows();
            if ($availability) {
                return false;
            } else {
                $this->db->where('id', $coupon_id);
                $this->db->update('coupons', $data);
                return true;
            }
        } else {
            return false;
        }
    }

    public function delete_coupon($coupon_id)
    {
        $this->db->where('id', $coupon_id);
        $this->db->delete('coupons');
        return true;
    }

    // CHECK IF THE COUPON CODE IS VALID
    public function check_coupon_validity($coupon_code)
    {
        $this->db->where('code', $coupon_code);
        $result = $this->db->get('coupons');
        if ($result->num_rows() > 0) {
            $result = $result->row_array();
            if ($result['expiry_date'] >= strtotime(date('D, d-M-Y'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // GET DISCOUNTED PRICE AFTER APPLYING COUPON
    public function get_discounted_price_after_applying_coupon($coupon_code)
    {
        $total_price  = 0;
        foreach ($this->session->userdata('cart_items') as $cart_item) {
            $course_details = $this->crud_model->get_course_by_id($cart_item)->row_array();
            if ($course_details['discount_flag'] == 1) {
                $total_price += $course_details['discounted_price'];
            } else {
                $total_price  += $course_details['price'];
            }
        }

        if ($this->check_coupon_validity($coupon_code)) {
            $coupon_details = $this->get_coupon_details_by_code($coupon_code)->row_array();
            $discounted_price = ($total_price * $coupon_details['discount_percentage']) / 100;
            $total_price = $total_price - $discounted_price;
        } else {
            return $total_price;
        }

        return $total_price > 0 ? $total_price : 0;
    }

    function get_free_lessons($lesson_id = "")
    {
        if ($lesson_id != "") {
            $this->db->where('id', $lesson_id);
        }
        $this->db->where('is_free', 1);
        return $this->db->get('lesson');
    }

    function get_watch_histories($user_id = 0, $course_id = 0)
    {
        if ($user_id > 0) {
            $this->db->where('student_id', $user_id);
        }
        if ($course_id > 0) {
            $this->db->where('course_id', $course_id);
        }
        return $this->db->get('watch_histories');
    }

    function update_last_played_lesson($course_id = "", $lesson_id = "")
    {
        $user_id = $this->session->userdata('user_id');
        $query = $this->db->get_where('watch_histories', array('course_id' => $course_id, 'student_id' => $user_id));

        if ($course_id != "") {
            if ($lesson_id == "") {
                $this->db->where('course_id', $course_id);
                $this->db->order_by('order', 'asc');
                $this->db->order_by('id', 'asc');
                $this->db->limit(1);
                $lesson_id = $this->db->get('lesson')->row('id');
            }
            if ($query->num_rows() > 0) {
                $this->db->where('watch_history_id', $query->row('watch_history_id'));
                $this->db->update('watch_histories', array('watching_lesson_id' => $lesson_id, 'date_updated' => time()));
            } else {
                $data['course_id'] = $course_id;
                $data['student_id'] = $user_id;
                $data['watching_lesson_id'] = $lesson_id;
                $data['date_added'] = time();
                $this->db->insert('watch_histories', $data);
            }
            return $lesson_id;
        } elseif ($query->num_rows() > 0) {
            return $query->row('watching_lesson_id');
        }
    }

    function update_watch_history_with_duration()
    {
        $course_progress = 0;
        $is_completed = 0;
        $data['watched_course_id'] = htmlspecialchars($this->input->post('course_id'));
        $data['watched_lesson_id'] = htmlspecialchars($this->input->post('lesson_id'));
        $data['watched_student_id'] = $this->session->userdata('user_id');

        $current_duration = htmlspecialchars($this->input->post('current_duration'));

        $current_history = $this->db->get_where('watched_duration', $data);


        if ($current_history->num_rows() > 0) {
            $current_history = $current_history->row_array();
            $watched_duration_arr = json_decode($current_history['watched_counter'], true);
            if (!is_array($watched_duration_arr)) $watched_duration_arr = array();
            if (!in_array($current_duration, $watched_duration_arr)) {
                array_push($watched_duration_arr, $current_duration);
            }

            $watched_duration_json = json_encode($watched_duration_arr);

            $this->db->where('watched_course_id', $data['watched_course_id']);
            $this->db->where('watched_lesson_id', $data['watched_lesson_id']);
            $this->db->where('watched_student_id', $data['watched_student_id']);
            $this->db->update('watched_duration', array('watched_counter' => $watched_duration_json, 'current_duration' => $current_duration));
        } else {
            $watched_duration_arr = array($current_duration);
            $data['current_duration'] = $current_duration;
            $data['watched_counter'] = json_encode($watched_duration_arr);
            $this->db->insert('watched_duration', $data);
        }

        $course_details = $this->db->get_where('course', ['id' => $data['watched_course_id']])->row_array();
        if($course_details['enable_drip_content'] != true){
            return json_encode(array('lesson_id' => $data['watched_lesson_id'], 'course_progress' => null, 'is_completed' => null));
        }


        $drip_content_settings = json_decode(get_settings('drip_content_settings'), true);
        $lesson_total_duration = $this->db->get_where('lesson', array('id' => $data['watched_lesson_id']))->row('duration');
        $lesson_total_duration = explode(':', $lesson_total_duration);
        $lesson_total_seconds = ($lesson_total_duration[0] * 3600) + ($lesson_total_duration[1] * 60) + $lesson_total_duration[2];
        $current_total_seconds = count($watched_duration_arr) * 5;

        if ($drip_content_settings['lesson_completion_role'] == 'duration') {
            if ($current_total_seconds >= $drip_content_settings['minimum_duration']) {
                $is_completed = 1;
            } elseif (($current_total_seconds + 4) >= $lesson_total_seconds) {
                $is_completed = 1;
            }
        } else {
            $required_duration = ($lesson_total_seconds / 100) * $drip_content_settings['minimum_percentage'];
            if ($current_total_seconds >= $required_duration) {
                $is_completed = 1;
            } elseif (($current_total_seconds + 4) >= $lesson_total_seconds) {
                $is_completed = 1;
            }
        }

        if ($is_completed == 1) {
            $query = $this->db->get_where('watch_histories', array('course_id' => $data['watched_course_id'], 'student_id' => $data['watched_student_id']));
            $course_progress = $query->row('course_progress');

            if ($query->num_rows() > 0) {
                $lesson_ids = json_decode($query->row('completed_lesson'), true);
                if (!is_array($lesson_ids)) $lesson_ids = array();
                if (!in_array($data['watched_lesson_id'], $lesson_ids)) {
                    array_push($lesson_ids, $data['watched_lesson_id']);
                    $total_lesson = $this->db->get_where('lesson', array('course_id' => $data['watched_course_id']))->num_rows();
                    $course_progress = (100 / $total_lesson) * count($lesson_ids);

                    $this->db->where('watch_history_id', $query->row('watch_history_id'));
                    $this->db->update('watch_histories', array('course_progress' => $course_progress, 'completed_lesson' => json_encode($lesson_ids), 'date_updated' => time()));

                    // CHECK IF THE USER IS ELIGIBLE FOR CERTIFICATE
                    if (addon_status('certificate') && $course_progress >= 100) {
                        $this->load->model('addons/Certificate_model', 'certificate_model');
                        $this->certificate_model->check_certificate_eligibility($data['watched_course_id'], $data['watched_student_id']);
                    }
                }
            }
        }
        return json_encode(array('lesson_id' => $data['watched_lesson_id'], 'course_progress' => round($course_progress), 'is_completed' => $is_completed));
    }

    function get_top_instructor($limit = 10)
    {
        $query = $this->db
            ->select("creator, count(*) AS enrol_number", false)
            ->from("enrol")
            ->join('course', 'course.id = enrol.course_id')
            ->group_by('creator')
            ->order_by("creator", "DESC")
            ->limit($limit)
            ->get();
        return $query->result_array();
    }

    function get_active_course_by_category_id($category_id = "", $category_id_type = "category_id")
    {
        $this->db->where($category_id_type, $category_id);
        $this->db->where('status', 'active');
        return $this->db->get('course');
    }
    function get_active_course($course_id = "")
    {

        if ($course_id > 0) {
            $this->db->where('id', $course_id = "");
        }
        $this->db->where('status', 'active');
        return $this->db->get('course');
    }


    function forgot_password()
    {
        $email = $this->input->post('email');
        $verification_code = str_replace('=', '', base64_encode($email . '_Uh6#@#6hU_' . rand(111111, 9999999)));
        $this->db->where('email', $email);
        $this->db->update('users', array('verification_code' => $verification_code, 'last_modified' => time()));
        // send new password to user email
        $this->email_model->password_reset_email($verification_code, $email);
        $this->session->set_flashdata('flash_message', get_phrase('check_your_inbox_for_the_request'));
    }

    function change_password_from_forgot_passord($verification_code = "")
    {
        $decoded_verification_code = explode('_Uh6#@#6hU_', base64_decode($verification_code));
        $email = $decoded_verification_code[0];

        $new_verification_code = str_replace('=', '', base64_encode($email . '_Uh6#@#6hU_' . rand(111111, 9999999)));

        $this->db->where('email', $email);
        $this->db->where('verification_code', $verification_code);
        $this->db->update('users', array('password' => sha1($this->input->post('new_password')), 'verification_code' => $new_verification_code));
        return true;
    }




    //Start Blog

    function add_blog_category()
    {
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['subtitle'] = htmlspecialchars($this->input->post('subtitle'));
        $data['slug'] = slugify($data['title']);
        $data['added_date'] = time();

        $this->db->where('slug', $data['slug']);
        $row = $this->db->get('blog_category');
        if ($row->num_rows() > 0) {
            return false;
        } else {
            $this->db->insert('blog_category', $data);
            return true;
        }
    }

    function update_blog_category($blog_category_id = "")
    {
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['subtitle'] = htmlspecialchars($this->input->post('subtitle'));
        $data['slug'] = slugify($data['title']);

        $this->db->where('slug', $data['slug']);
        $row = $this->db->get('blog_category');
        if ($row->num_rows() > 0 && $row->row('blog_category_id') != $blog_category_id) {
            return false;
        } else {
            $this->db->where('blog_category_id', $blog_category_id);
            $this->db->update('blog_category', $data);
            return true;
        }
    }

    function delete_blog_category($blog_category_id = "")
    {
        $this->db->where('blog_category_id', $blog_category_id);
        $this->db->delete('blog_category');
    }

    function get_blog_categories($blog_category_id = "")
    {
        if ($blog_category_id > 0) {
            $this->db->where('blog_category_id', $blog_category_id);
        }
        return $this->db->get('blog_category');
    }

    function get_blog_category_by_slug($ctaegory_slug = "")
    {
        $this->db->where('slug', $ctaegory_slug);
        return $this->db->get('blog_category');
    }

    function get_all_blogs($blog_id = "")
    {
        if ($blog_id > 0) {
            $this->db->where('blog_id', $blog_id);
        }
        $this->db->order_by('blog_id', 'desc');
        return $this->db->get('blogs');
    }

    function get_blogs($blog_id = "")
    {
        if ($blog_id > 0) {
            $this->db->where('blog_id', $blog_id);
        }
        $this->db->where('status !=', 'pending');
        $this->db->order_by('blog_id', 'desc');
        return $this->db->get('blogs');
    }

    function get_active_blogs($blog_id = "")
    {
        if ($blog_id > 0) {
            $this->db->where('blog_id', $blog_id);
        }
        $this->db->where('status', 1);
        return $this->db->get('blogs');
    }

    function get_instructors_pending_blog($user_id = "")
    {
        if ($user_id > 0) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('status', 'pending');
        return $this->db->get('blogs');
    }

    function get_popular_blogs($limit = 10)
    {
        $this->db->order_by("blog_id", "desc");
        $this->db->limit($limit);
        $this->db->where('status', 1);
        $this->db->where('is_popular', 1);
        return $this->db->get('blogs');
    }

    function get_latest_blogs($limit = 10)
    {
        $this->db->order_by("blog_id", "desc");
        $this->db->limit($limit);
        $this->db->where('status', 1);
        return $this->db->get('blogs');
    }

    function get_blogs_by_category_id($blog_category_id = "")
    {
        $this->db->where('blog_category_id', $blog_category_id);
        $this->db->where('status', 1);
        return $this->db->get('blogs');
    }

    function get_blogs_by_user_id($user_id = "", $status = "")
    {
        $this->db->where('user_id', $user_id);
        if ($status == '') {
            $this->db->where('status !=', 'pending');
        } else {
            $this->db->where('status', $status);
        }
        return $this->db->get('blogs');
    }

    function get_categories_with_blog_number($limit = 10)
    {
        $query = $this->db
            ->select("blog_category_id, count(*) AS blog_number", false)
            ->from("blogs")
            ->group_by('blog_category_id')
            ->order_by("blog_number", "DESC")
            ->where('status', 1)
            ->limit($limit)
            ->get();
        return $query->result_array();
    }

    function get_blog_comments_by_blog_id($blog_id = "")
    {
        $this->db->where('parent_id <', 1);
        $this->db->where('blog_id', $blog_id);
        $this->db->order_by('added_date', 'desc');
        return $this->db->get('blog_comments');
    }

    function get_blog_comments_by_parent_id($parent_id = "")
    {
        $this->db->where('parent_id', $parent_id);
        $this->db->order_by('added_date', 'asc');
        return $this->db->get('blog_comments');
    }

    function add_blog_comment($blog_id = "", $user_id = "")
    {
        $data['comment'] = htmlspecialchars($this->input->post('comment'));
        $data['parent_id'] = $this->input->post('parent_id');
        $data['added_date'] = time();
        $data['blog_id'] = $blog_id;
        $data['user_id'] = $user_id;
        $this->db->insert('blog_comments', $data);
    }

    function update_blog_comment($blog_comment_id = "", $user_id = "")
    {
        $data['comment'] = htmlspecialchars($this->input->post('comment'));
        $data['updated_date'] = time();

        if (!$this->session->userdata('admin_login')) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('blog_comment_id', $blog_comment_id);
        $this->db->update('blog_comments', $data);
    }
    function delete_comment($blog_comment_id = "", $user_id = "")
    {
        if (!$this->session->userdata('admin_login')) {
            $this->db->where('user_id', $user_id);
        }
        $this->db->where('blog_comment_id', $blog_comment_id);
        $this->db->delete('blog_comments');
    }

    //Start for admin panel
    function add_blog()
    {
        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['blog_category_id'] = htmlspecialchars($this->input->post('blog_category_id'));
        $data['keywords'] = htmlspecialchars($this->input->post('keywords'));
        $data['description'] = htmlspecialchars(remove_js($this->input->post('description', false)));
        $data['added_date'] = time();
        $data['user_id'] = $this->session->userdata('user_id');

        if ($this->session->userdata('admin_login')) {
            $data['is_popular'] = htmlspecialchars($this->input->post('is_popular'));
            $data['status'] = 1;
        } else {
            $data['status'] = 'pending';
            $data['is_popular'] = 0;
        }

        if ($_FILES['thumbnail']['name'] != "") {
            $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.png';
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/blog/thumbnail/' . $data['thumbnail']);
        }
        if ($_FILES['banner']['name'] != "") {
            $data['banner'] = md5(rand(10000000, 20000000)) . '.png';
            move_uploaded_file($_FILES['banner']['tmp_name'], 'uploads/blog/banner/' . $data['banner']);
        }

        $this->db->insert('blogs', $data);
    }

    function update_blog($blog_id = "")
    {
        $blog = $this->get_blogs($blog_id)->row_array();

        $data['title'] = htmlspecialchars($this->input->post('title'));
        $data['blog_category_id'] = htmlspecialchars($this->input->post('blog_category_id'));
        $data['keywords'] = htmlspecialchars($this->input->post('keywords'));
        $data['description'] = htmlspecialchars(remove_js($this->input->post('description', false)));
        $data['updated_date'] = time();

        if ($this->session->userdata('admin_login')) {
            $data['is_popular'] = htmlspecialchars($this->input->post('is_popular'));
        }

        if ($_FILES['thumbnail']['name'] != "") {
            unlink('uploads/blog/thumbnail/' . $blog['thumbnail']);

            $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.png';
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/blog/thumbnail/' . $data['thumbnail']);
        }
        if ($_FILES['banner']['name'] != "") {
            unlink('uploads/blog/banner/' . $blog['banner']);

            $data['banner'] = md5(rand(10000000, 20000000)) . '.png';
            move_uploaded_file($_FILES['banner']['tmp_name'], 'uploads/blog/banner/' . $data['banner']);
        }

        $this->db->where('blog_id', $blog_id);
        $this->db->update('blogs', $data);
    }

    function update_blog_status($blog_id = "")
    {
        $current_status = $this->get_blogs($blog_id)->row('status');
        if ($current_status == 1) :
            $this->db->where('blog_id', $blog_id);
            $this->db->update('blogs', array('status' => 0));
        else :
            $this->db->where('blog_id', $blog_id);
            $this->db->update('blogs', array('status' => 1));
        endif;
    }

    function approve_blog($blog_id = "")
    {
        $this->db->where('blog_id', $blog_id);
        $this->db->update('blogs', array('status' => 1));
    }

    function blog_delete($blog_id = "")
    {
        $blog = $this->get_blogs()->row_array();
        unlink('uploads/blog/banner/' . $blog['banner']);
        unlink('uploads/blog/thumbnail/' . $blog['thumbnail']);

        $this->db->where('blog_id', $blog_id);
        $this->db->delete('blogs');

        $this->db->where('blog_id', $blog_id);
        $this->db->delete('blog_comments');
    }

    function update_blog_settings()
    {
        $data['value'] = htmlspecialchars($this->input->post('blog_page_title'));
        $this->db->where('key', 'blog_page_title');
        $this->db->update('frontend_settings', $data);

        $data['value'] = htmlspecialchars($this->input->post('blog_page_subtitle'));
        $this->db->where('key', 'blog_page_subtitle');
        $this->db->update('frontend_settings', $data);

        $data['value'] = htmlspecialchars($this->input->post('instructors_blog_permission'));
        $this->db->where('key', 'instructors_blog_permission');
        $this->db->update('frontend_settings', $data);

        $data['value'] = htmlspecialchars($this->input->post('blog_visibility_on_the_home_page'));
        $this->db->where('key', 'blog_visibility_on_the_home_page');
        $this->db->update('frontend_settings', $data);

        if ($_FILES['blog_page_banner']['name'] != "") {
            unlink('uploads/blog/page-banner/' . get_frontend_settings('blog_page_banner'));

            $data['value'] = md5(rand(10000000, 20000000)) . '.png';
            $this->db->where('key', 'blog_page_banner');
            $this->db->update('frontend_settings', $data);

            move_uploaded_file($_FILES['blog_page_banner']['tmp_name'], 'uploads/blog/page-banner/' . $data['value']);
        }
    }
    //End Blog


    function get_quiz_score($course_id = "", $quiz_id = "")
    {
        $this->db->where('student_id', $this->session->userdata('user_id'));
        $this->db->where('course_id', $course_id);
        $query = $this->db->get('watch_histories');

        if ($query->num_rows() > 0) {
            $previous_result = json_decode($query->row('quiz_result'), true);
            if ($quiz_id > 0) {
                if (is_array($previous_result) && array_key_exists($quiz_id, $previous_result)) {
                    return $previous_result[$quiz_id];
                } else {
                    return 'no_result';
                }
            } else {
                if (is_array($previous_result) && count($previous_result) > 0) {
                    return array_sum($previous_result);
                } else {
                    return 'no_result';
                }
            }
        } else {
            return 'no_result';
        }
    }


    function save_drip_content_settings()
    {
        $settings_data['lesson_completion_role'] = htmlspecialchars($this->input->post('lesson_completion_role'));
        $time = htmlspecialchars($this->input->post('minimum_duration'));
        $time = explode(':', $time);
        $seconds = ($time[0] * 3600) + ($time[1] * 60) + $time[2];
        $settings_data['minimum_duration'] = $seconds;
        $settings_data['minimum_percentage'] = htmlspecialchars($this->input->post('minimum_percentage'));
        $settings_data['locked_lesson_message'] = htmlspecialchars($this->input->post('locked_lesson_message', false));

        $data['value'] = json_encode($settings_data);

        $this->db->where('key', 'drip_content_settings');
        $this->db->update('settings', $data);
    }

    function get_custom_pages($custom_page_id = '', $button_position = "")
    {
        if ($custom_page_id > 0) {
            $this->db->where('custom_page_id', $custom_page_id);
        }

        if ($button_position != "") {
            $this->db->where('button_position', $button_position);
        }
        return $this->db->get('custom_page');
    }

    function add_custom_page()
    {
        $data['page_title'] = htmlspecialchars($this->input->post('page_title'));
        $data['page_content'] = htmlspecialchars($this->input->post('page_content', false));
        $data['page_url'] = slugify($this->input->post('page_url'));
        $data['button_title'] = htmlspecialchars($this->input->post('button_title'));
        $data['button_position'] = htmlspecialchars($this->input->post('button_position'));
        $data['status'] = 1;

        $this->db->insert('custom_page', $data);
    }

    function update_custom_page($custom_page_id = "")
    {
        $data['page_title'] = htmlspecialchars($this->input->post('page_title'));
        $data['page_content'] = htmlspecialchars($this->input->post('page_content', false));
        $data['page_url'] = slugify($this->input->post('page_url'));
        $data['button_title'] = htmlspecialchars($this->input->post('button_title'));
        $data['button_position'] = htmlspecialchars($this->input->post('button_position'));

        $this->db->where('custom_page_id', $custom_page_id);
        $this->db->update('custom_page', $data);
    }

    function delete_custom_page($custom_page_id = "")
    {
        $this->db->where('custom_page_id', $custom_page_id);
        $this->db->delete('custom_page');
    }

    function is_course_instructor($course_id = "", $user_id = "")
    {
        $course_details = $this->get_course_by_id($course_id)->row_array();

        if ($course_details['creator'] == $user_id) {
            return true;
        } elseif ($course_details['multi_instructor'] == 1) {
            $user_ids = explode(',', $course_details['user_id']);
            if (in_array($user_id, $user_ids)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    

    
}
