<?php
$quiz_questions = $this->crud_model->get_quiz_questions($lesson_details['id']);
?>
<div id="loading-view" class="text-center" style=" height: 200px; padding-top: 75px; display: none;">
    <img src="https://gifimage.net/wp-content/uploads/2017/08/loading-gif-transparent-25.gif" alt="" height="50" id = "calculating_result">
</div>
<div id="quiz-stuffs" style="font-size: 40px;">
    <div id="quiz-body" class="text-center py-4">
        <div class="p-2" id="quiz-header">
            <?php echo get_phrase("quiz_title"); ?> : <strong><?php echo $lesson_details['title']; ?></strong><br>
            <?php echo get_phrase("number_of_questions"); ?> : <strong><?php echo count($quiz_questions->result_array()); ?></strong><br>
            <?php if (count($quiz_questions->result_array()) > 0): ?>
                <button type="button" name="button" class="btn red mt-2" style="color: #fff; font-size: 30px;" onclick="getStarted(1)"><?php echo get_phrase("get_started"); ?></button>
            <?php endif; ?>
        </div>

        <form class="" id = "quiz_form" action="" method="post">
            <?php if (count($quiz_questions->result_array()) > 0): ?>
                <?php foreach ($quiz_questions->result_array() as $key => $quiz_question):
                    $options = json_decode($quiz_question['options']);
                    ?>
                    <input type="hidden" name="lesson_id" value="<?php echo $lesson_details['id']; ?>">
                    <div class="hidden" id = "question-number-<?php echo $key+1; ?>">
                        <div class="row justify-content-center">
                            <div class="col-lg-12">
                                <div class="card text-left quiz-card">
                                    <div class="card-body">
                                        <h2 class="card-title"><?php echo get_phrase("question").' '.($key+1); ?> : <strong><?php echo $quiz_question['title']; ?></strong></h2>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <?php
                                        foreach ($options as $key2 => $option): ?>
                                        <li class="list-group-item quiz-options">
                                            <div class="form-check text-start">
                                                <input class="form-check-input" type="checkbox" name="<?php echo $quiz_question['id']; ?>[]" value="<?php echo $key2+1; ?>" id="quiz-id-<?php echo $quiz_question['id']; ?>-option-id-<?php echo $key2+1; ?>" onclick="enableNextButton('<?php echo $quiz_question['id'];?>')">
                                                <label class="form-check-label" for="quiz-id-<?php echo $quiz_question['id']; ?>-option-id-<?php echo $key2+1; ?>">
                                                    <?php echo $option; ?>
                                                </label>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <button type="button" name="button" class="btn red mt-2 mb-2" id = "next-btn-<?php echo $quiz_question['id'];?>" style="color: #fff; font-size: 30px;" <?php if(count($quiz_questions->result_array()) == $key+1):?>onclick="submitQuiz()"<?php else: ?>onclick="showNextQuestion('<?php echo $key+2; ?>')"<?php endif; ?> disabled><?php echo count($quiz_questions->result_array()) == $key+1 ? get_phrase("check_result") : get_phrase("submit_and_next"); ?></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </form>
    </div>
    <div id="quiz-result" class="text-left">

    </div>
    <div class="" style="margin: 20px 0;" id = "lesson-summary">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title"><?php echo $lesson_details['lesson_type'] == 'quiz' ? get_phrase('instruction') : get_phrase("note"); ?>:</h3>
                <?php if ($lesson_details['summary'] == ""): ?>
                    <p class="card-text"><?php echo $lesson_details['lesson_type'] == 'quiz' ? get_phrase('no_instruction_found') : get_phrase("no_summary_found"); ?></p>
                <?php else: ?>
                    <p class="card-text"><?php echo $lesson_details['summary']; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style media="screen">
    #quiz-body {
        overflow-x: hidden;
    }
</style>

<script type="text/javascript">
function getStarted(first_quiz_question) {
    $('#quiz-header').hide();
    $('#lesson-summary').hide();
    $('#question-number-'+first_quiz_question).show();
}
function showNextQuestion(next_question) {
    $('#question-number-'+(next_question-1)).hide();
    $('#question-number-'+next_question).show();
}
function submitQuiz() {
    $(window).scrollTop(0);
    $("html, body").animate({ scrollTop: 0 }, "slow");
    $("#loading-view").show();
    $("#quiz-stuffs").hide();

    $.ajax({
        url: '<?php echo site_url('home/submit_quiz/mobile'); ?>',
        type: 'post',
        data: $('form#quiz_form').serialize(),
        success: function(response) {
            $("#quiz-stuffs").show();
            $('#quiz-body').hide();
            $('#quiz-result').html(response);
            $("#loading-view").hide();
        }
    });
}
function enableNextButton(quizID) {
    $('#next-btn-'+quizID).prop('disabled', false);
}
</script>
