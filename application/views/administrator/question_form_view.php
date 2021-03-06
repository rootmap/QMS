<h1 class="heading">
    <?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> 
    Question
</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/question/add_question', array('class' => 'form-horizontal', 'id' => 'question-form')); ?>
<?php else : ?>
<?php echo form_open('administrator/question/update_question', array('class' => 'form-horizontal', 'id' => 'question-form')); ?>
<?php endif; ?>

<script type="text/javascript" src="<?php echo base_url('assets/editor/generic_wiris/core/display.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/editor/generic_wiris/wirisplugin-generic.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/editor/ckeditor/ckeditor.js'); ?>"></script>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="ques_text">Question</label>
            <div class="controls">
                <textarea name="ques_text" id="ques_text" rows="5" cols="30" class="input-xxlarge"><?php echo $this->form_data->ques_text; ?></textarea>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="cat_parent">Select Category</label>
            <div class="controls">
                <select name="category_id" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="sub_cat_parent">Select Sub Category</label>
            <div class="controls">
                <select name="sub_cat_parent"  id="lf_sub_cat_parent"  class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label"  for="sub_two_cat_parents">Select Sub 2 Category</label>
            <div class="controls">
                <select name="sub_two_cat_parent" id="lf_sub_two_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label"  for="sub_three_cat_parent">Select Sub 3 Category</label>
            <div class="controls">
                <select name="sub_three_cat_parent" id="lf_sub_three_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label"  for="sub_three_cat_parent">Select Sub 4 Category</label>
            <div class="controls">
                <select name="sub_four_cat_parent" id="lf_sub_four_cat_parent" class="chosen-select input-xlarge">
                    <option value="">Please Select</option>
                </select>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="ques_expiry_date">Expiry Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="ques_expiry_date" id="ques_expiry_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('ques_expiry_date', $this->form_data->ques_expiry_date); ?>"
                        class="date input-small" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>
        <div class="control-group formSep">
            <label class="control-label" for="marks">Question Marks</label>
            <div class="controls">
                <input type="text" value="<?php echo $this->form_data->marks; ?>" name="marks" id="marks">
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="neg_marks">Negative Marks</label>
            <div class="controls">
                <input type="text" value="<?php echo $this->form_data->neg_marks; ?>" name="neg_marks" id="neg_marks">
            </div>
        </div>



        <div class="control-group formSep">
            <label class="control-label" for="is_mandatory">Is Mandatory</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="ques_mandatory" id="is_mandatory" value="1" <?php echo set_radio('ques_mandatory', '1', $this->form_data->ques_mandatory == '1'); ?> /> Mandatory
                </label>
                <label class="radio inline">
                    <input type="radio" name="ques_mandatory" id="no_mandatory" value="2" <?php echo set_radio('ques_mandatory', '2', $this->form_data->ques_mandatory == '2'); ?> /> Not Mandatory
                </label>
            </div>
        </div>

        

        <div class="control-group">
            <label class="control-label" for="ques_type1">Question Type</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="ques_type" id="ques_type2" value="descriptive" checked=""  <?php echo set_radio('ques_type', 'descriptive', $this->form_data->ques_type == 'descriptive'); ?> /> Descriptive Question
                </label>
                <label class="radio inline">
                    <input type="radio" name="ques_type" id="ques_type1" value="mcq" <?php echo set_radio('ques_type', 'mcq', $this->form_data->ques_type == 'mcq'); ?> /> Multiple Choice Question (MCQ)
                </label>

            </div>
        </div>





        <div class="control-group" id="mcqchoice-row">
            <label class="control-label" for="ques_type1">
                MCQ Options<br /><br />
                <a href="#AddChoiceModal" role="button" class="btn" data-toggle="modal">Add Choices</a><br /><br />
                <a href="#" role="button" class="btn" data-toggle="modal" id="clear-choices">Clear All Choices</a>
            </label>
            <div class="controls" id="mcqchoice-div">
            <!--<pre><?php /*//print_r( $this->form_data->ques_choices ); */?></pre>-->

            <?php if ($is_edit): ?>

                <?php if ($this->form_data->ques_choices != '' && count($this->form_data->ques_choices) > 0) : ?>

                    <?php for ($i=0; $i<count($this->form_data->ques_choices); $i++):
                        $text = $this->form_data->ques_choices[$i]['text'];
                        $is_answered = (int)$this->form_data->ques_choices[$i]['is_answer'];

                        $class_name = '';
                        $checked = '';
                        if ($is_answered) {
                            $class_name = ' is-answer';
                            $checked = ' checked="checked"';
                        }
                    ?>

                    <div class="choice<?php echo $class_name; ?>">
                        <div style="width:92%;overflow:hidden;">
                            <input type="checkbox" name="mcq_options_right[]" id="mcq_options_right_<?php echo $i+1; ?>" value="<?php echo $i; ?>" <?php echo $checked; ?> />
                            <label for="mcq_options_right_<?php echo $i+1; ?>">Correct Answer</label>

                            <a href="javascript:void(0)" class="move icon-move">move</a>
                            <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                            <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                        </div>
                        <textarea name="mcq_options[]" rows="3" cols="30" style="width:90%;"><?php echo $text; ?></textarea>
                    </div>
                    <?php endfor; ?>

                <?php else: ?>

                    <div class="choice">
                        <div style="width:92%;overflow:hidden;">
                            <input type="checkbox" name="mcq_options_right[]" id="mcq_options_right_1" value="0" />
                            <label for="mcq_options_right_1">Correct Answer</label>

                            <a href="javascript:void(0)" class="move icon-move">move</a>
                            <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                            <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                        </div>
                        <textarea name="mcq_options[]" rows="3" cols="30" style="width:90%;"></textarea>
                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="choice">
                    <div style="width:92%;overflow:hidden;">
                        <input type="checkbox" name="mcq_options_right[]" id="mcq_options_right_1" value="0" />
                        <label for="mcq_options_right_1"><span class="loopCounter">1.</span> Correct Answer</label>

                        <a href="javascript:void(0)" class="move icon-move">move</a>
                        <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                        <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                    </div>
                    <textarea name="mcq_options[]"  class="dup_check"  rows="3" cols="30" style="width:90%;"></textarea>
                </div>

            <?php endif; ?>

            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="question_id" value="<?php echo set_value('question_id', $this->form_data->question_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Question" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<div class="modal hide fade" id="AddChoiceModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Add Multiple Choices Collectively</h3>
    </div>
    <div class="modal-body">
        <p>Enter multiple choice options in the textarea below. Separate each choice by new line.</p>
        <textarea rows="10" cols="30" class="input-block-level" id="collective-mcq"></textarea>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-primary" id="add-choice-button">Add Choices</a>
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true" class="close-button">Close</a>
    </div>
</div>

<script>
           CKEDITOR.replace( 'ques_text' );
           //CKEDITOR.replace( 'answer', { toolbar : [ [ 'EqnEditor', 'Bold', 'Italic' ] ] });
           //CKEDITOR.replace( 'answer', { toolbar : [ [ 'EqnEditor', 'Bold', 'Italic' ] ] });



        </script>

<script type="text/javascript">
jQuery(document).ready(function(){


    /* show-hide choice options div */
    if (jQuery('#ques_type1').is(':checked')) {
        jQuery('#mcqchoice-row').show();
        jQuery('#mcq_neg_mark').show();
    } else {
        jQuery('#mcqchoice-row').hide();
        jQuery('#mcq_neg_mark').hide();
    }

    jQuery('#ques_type1').click(function(){
        jQuery('#mcqchoice-row').show();
        jQuery('#mcq_neg_mark').show();
    });

    jQuery('#ques_type2').click(function(){
        jQuery('#mcqchoice-row').hide();
        jQuery('#mcq_neg_mark').hide();
    });


    /* sort choice div */
    jQuery('#mcqchoice-div').sortable({
        update: function(event, ui) {
            jQuery('#mcqchoice-div .choice').each(function(index){
                jQuery(this).find('input').val(index);
                rearrangeChoiceButtons();
            });
        }
    });


    /* highlight is_answer choice */
    jQuery('.choice input').live('change', function (){
        if (jQuery(this).is(':checked')) {
            jQuery(this).parent().parent().addClass('is-answer');
        } else {
            jQuery(this).parent().parent().removeClass('is-answer');
        }
    });


    rearrangeChoiceButtons();

    /* add choice button */
    jQuery('.choice .add').live('click', function(){
        addNewChoice();
        rearrangeChoiceButtons();
        
    });

    /* remove choice button */
    jQuery('.choice .delete').live('click', function(){
        jQuery(this).parent().parent().find('textarea').val('');
        jQuery(this).parent().parent().hide();
        rearrangeChoiceButtons();
        
    });


    /* multiple choice add modal button */
    jQuery('#AddChoiceModal').on('shown', function () {
        jQuery('#collective-mcq').focus();
        
    });

    jQuery('#AddChoiceModal').on('hide', function () {
        jQuery('#collective-mcq').val('');
        
    });

    jQuery('#add-choice-button').live('click', function(){

        var mcqOptions = jQuery('#collective-mcq').val();
        var mcqOptionsArr = mcqOptions.split('\n');
        var mcqChoiceText = '';

        for (i = 0; i<mcqOptionsArr.length; i++) {
            mcqChoiceText = jQuery.trim(mcqOptionsArr[i]);
            if (mcqChoiceText != '') {
                addNewChoice(mcqChoiceText);
                console.log(mcqChoiceText);
            }
        }

        rearrangeChoiceButtons();
        jQuery('#AddChoiceModal').modal('hide');
        
        return false;
    });

    /* clear choice button */
    jQuery('#clear-choices').click(function(){
        var questionNumber=1;
        var response = confirm('This is will remove all choices. Are you sure you want to continue?');
        if (response) {
            jQuery('#mcqchoice-div .choice').each(function(){
                jQuery(this).remove();
            });

            addNewChoice();
            rearrangeChoiceButtons();
        }

        
    });


    /* submit validation */
    jQuery('#question-form').submit(function(){



        /*var questionText = jQuery.trim(jQuery('#ques_text').val());
        console.log(questionText);
        if (questionText == '') {
            alert('Question is required');
            jQuery('#ques_text').focus();
            return false;
        }*/




        if (jQuery('#ques_type1').is(':checked')) {

            var totalChoice = 0;
            jQuery('.choice textarea').each(function(){
                var t = jQuery.trim(jQuery(this).val());
                if (t != '') { totalChoice = totalChoice + 1; }
            });

             var valid = true;

       

            if (totalChoice < 2) {
                alert('Please enter at least two choices');
                return false;
            }

            var isAnswerChecked = false;
            jQuery('.choice input[type="checkbox"]').each(function(){
                if(jQuery(this).is(':checked')) {
                    isAnswerChecked = true;
                }
            });

            if (!isAnswerChecked) {
                alert('Please mark a choice as Correct Answer');
                return false;
            }

             $.each($('.choice textarea'), function (index1, item1) {

            $.each($('.choice textarea').not(this), function (index2, item2) {

                if ($(item1).val() == $(item2).val()) {
                   
                    valid = false;
                }

            });
        });

        if(valid === false)
        {
            alert('Multiple Question\'s answer should be unique');
                return false;
        }
        }
    });

});

var questionNumber=1;

function addNewChoice(text) {

    var totalChoice = parseInt(jQuery('#mcqchoice-div .choice').length);
    var choiceHtml = '';
    var text = jQuery.trim(text);
    questionNumber++;
    choiceHtml += '<div class="choice"> ';
        choiceHtml += '<div style="width:92%;overflow:hidden;">';
            choiceHtml += ' <input type="checkbox" name="mcq_options_right[]" id="mcq_options_right_'+ (totalChoice + 1) +'" value="'+ totalChoice +'" />';
            choiceHtml += '<label for="mcq_options_right_'+ (totalChoice + 1) +'"><span class="loopCounter">'+((totalChoice-0)+(1-0))+'.</span> Correct Answer</label>';
            choiceHtml += '<a href="javascript:void(0)" class="move icon-move" title="Sort MCQ Option">move</a>';
            choiceHtml += '<a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>';
            choiceHtml += '<a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>';
        choiceHtml += '</div>';
        choiceHtml += '<textarea name="mcq_options[]" class="dup_check" rows="3" cols="30" style="width:90%;">'+ text +'</textarea>';
    choiceHtml += '</div>';

    

    jQuery('#mcqchoice-div').append(choiceHtml);
    
    return false;

}



function rearrangeChoiceButtons() {
    jQuery('.choice .add').css('display', 'none');
    jQuery('.choice .delete').css('display', 'inline-block');
    jQuery('.choice:last-child .add').css('display', 'inline-block');
    jQuery('.choice:last-child .delete').css('display', 'none');
    
}




</script>

<script type="text/javascript">

    function loadCategory()
    {
        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("Counter Display Status : "+data);

                var htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Category</option>';
                    $.each(data,function(key,row){
                        htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                    });
                }

                $("select[name=category_id]").html(htmlString).chosen();
                $('select[name=category_id]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubCategory(cid)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                        }
                        
                    });
                }

                $("select[name=sub_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubTwoCategory(cid,SubCat)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_two_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 2 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                            }
                        }
                        
                    });
                }

                console.log(htmlString);

                $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubThreeCategory(cid,SubCat,SubTwoCat)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_three_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 3 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.sub_two_cat_parent==SubTwoCat)
                                {
                                    htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubFourCategory(cid,SubCat,SubTwoCat,SubThreeCat)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_four_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_four_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 4 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.sub_two_cat_parent==SubTwoCat)
                                {
                                    if(row.sub_three_cat_parent==SubThreeCat)
                                    {
                                         htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                    }
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_four_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_four_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    $(document).ready(function(){
        
        loadCategory();
        

        $("select[name=category_id]").change(function(){
            var cat_parent=$(this).val();
            if(cat_parent.length>0)
            {
                loadSubCategory(cat_parent);
            }
        });

      
        $("#lf_sub_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            //console.log(cat_parent);
            //return false;
            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$(this).val();
            if(sub_cat_parent.length>0)
            {
                loadSubTwoCategory(cat_parent,sub_cat_parent);
            }
            
        });
        
        $("#lf_sub_two_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$("#lf_sub_cat_parent").val();

            if(sub_cat_parent.length==0)
            {
                alert("Please select a Sub Category.");
                return false;
            }

            var sub_two_cat_parent=$(this).val();
            if(sub_two_cat_parent.length>0)
            {
                loadSubThreeCategory(cat_parent,sub_cat_parent,sub_two_cat_parent);
            }
            
        });

        $("#lf_sub_three_cat_parent").change(function(){
            var cat_parent=$("select[name=category_id]").val();

            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$("#lf_sub_cat_parent").val();

            if(sub_cat_parent.length==0)
            {
                alert("Please select a Sub Category.");
                return false;
            }

            var sub_two_cat_parent=$("#lf_sub_two_cat_parent").val();

            if(sub_two_cat_parent.length==0)
            {
                alert("Please select a Sub 2 Category.");
                return false;
            }

            var sub_three_cat_parent=$(this).val();
            if(sub_three_cat_parent.length>0)
            {
                loadSubFourCategory(cat_parent,sub_cat_parent,sub_two_cat_parent,sub_three_cat_parent);
            }
            
        });
       
        
    });

   

</script>


